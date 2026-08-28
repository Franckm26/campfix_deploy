<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->backfillReporterSnapshots();
        $this->preserveOwnedRecords('concerns');
        $this->preserveOwnedRecords('reports');
        $this->protectReporterSnapshots();
    }

    public function down(): void
    {
        // Restoring cascade deletion would make historical submissions unsafe.
        // Keep SET NULL semantics when rolling back application releases.
    }

    private function preserveOwnedRecords(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'user_id')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(sprintf('ALTER TABLE %s ALTER COLUMN user_id DROP NOT NULL', $table));
            DB::unprepared(sprintf(<<<'SQL'
DO $$
DECLARE fk_name text;
BEGIN
    FOR fk_name IN
        SELECT tc.constraint_name
        FROM information_schema.table_constraints tc
        JOIN information_schema.key_column_usage kcu
          ON tc.constraint_name = kcu.constraint_name
         AND tc.table_schema = kcu.table_schema
        WHERE tc.table_schema = 'public'
          AND tc.table_name = '%s'
          AND tc.constraint_type = 'FOREIGN KEY'
          AND kcu.column_name = 'user_id'
    LOOP
        EXECUTE format('ALTER TABLE public.%s DROP CONSTRAINT %%I', fk_name);
    END LOOP;
END $$;
SQL, $table, $table));
            DB::statement(sprintf(
                'ALTER TABLE %s ADD CONSTRAINT %s_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL',
                $table,
                $table
            ));

            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropForeign(['user_id']);
        });
        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->unsignedBigInteger('user_id')->nullable()->change();
            $blueprint->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    private function backfillReporterSnapshots(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            $this->backfillPostgresTable('concerns', 'reporter_name');
            $this->backfillPostgresTable('reports', 'reported_by_name');

            return;
        }

        foreach ([['concerns', 'reporter_name'], ['reports', 'reported_by_name']] as [$table, $nameColumn]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $nameColumn)) {
                continue;
            }

            DB::table($table)
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->chunkById(100, function ($records) use ($table, $nameColumn): void {
                    foreach ($records as $record) {
                        $user = DB::table('users')->where('id', $record->user_id)->first();
                        if (! $user) {
                            continue;
                        }

                        $values = [
                            $nameColumn => $user->name ?? 'Unknown',
                            'reporter_email' => $user->email ?? null,
                            'reporter_role' => $user->role ?? null,
                            'reporter_department' => $user->department ?? null,
                            'reporter_phone' => $user->phone ?? null,
                            'reporter_student_id' => $user->student_id ?? null,
                        ];

                        $values = array_filter(
                            $values,
                            fn ($value, $column) => Schema::hasColumn($table, $column)
                                && $record->{$column} === null,
                            ARRAY_FILTER_USE_BOTH
                        );

                        if ($values !== []) {
                            DB::table($table)->where('id', $record->id)->update($values);
                        }
                    }
                });
        }
    }

    private function backfillPostgresTable(string $table, string $nameColumn): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $nameColumn)) {
            return;
        }

        $hasActivityHistory = Schema::hasTable('activity_logs')
            && Schema::hasColumns('activity_logs', ['item_user_id', 'action', 'old_values', 'created_at']);

        $historicalValue = function (string $field) use ($table, $hasActivityHistory): string {
            if (! $hasActivityHistory) {
                return 'NULL';
            }

            return sprintf(
                "(SELECT log.old_values->>'%s' FROM activity_logs log WHERE log.item_user_id = records.user_id AND log.action = 'user_updated' AND log.created_at >= records.created_at AND log.old_values->>'%s' IS NOT NULL ORDER BY log.created_at ASC LIMIT 1)",
                $field,
                $field
            );
        };

        $assignments = [
            $nameColumn => "COALESCE(records.{$nameColumn}, {$historicalValue('name')}, users.name, 'Unknown')",
            'reporter_email' => "COALESCE(records.reporter_email, {$historicalValue('email')}, users.email)",
            'reporter_role' => "COALESCE(records.reporter_role, {$historicalValue('role')}, users.role)",
            'reporter_department' => "COALESCE(records.reporter_department, {$historicalValue('department')}, users.department)",
            'reporter_phone' => "COALESCE(records.reporter_phone, {$historicalValue('phone')}, users.phone)",
            'reporter_student_id' => "COALESCE(records.reporter_student_id, {$historicalValue('student_id')}, users.student_id)",
        ];

        $assignments = array_filter(
            $assignments,
            fn ($value, $column) => Schema::hasColumn($table, $column),
            ARRAY_FILTER_USE_BOTH
        );

        if ($assignments === []) {
            return;
        }

        $setSql = collect($assignments)
            ->map(fn ($expression, $column) => $column.' = '.$expression)
            ->implode(', ');

        DB::statement("UPDATE {$table} records SET {$setSql} FROM users WHERE users.id = records.user_id");
    }

    private function protectReporterSnapshots(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION public.preserve_concern_reporter_snapshot()
RETURNS trigger AS $$
BEGIN
    IF OLD.reporter_name IS NOT NULL THEN NEW.reporter_name := OLD.reporter_name; END IF;
    IF OLD.reporter_email IS NOT NULL THEN NEW.reporter_email := OLD.reporter_email; END IF;
    IF OLD.reporter_role IS NOT NULL THEN NEW.reporter_role := OLD.reporter_role; END IF;
    IF OLD.reporter_department IS NOT NULL THEN NEW.reporter_department := OLD.reporter_department; END IF;
    IF OLD.reporter_phone IS NOT NULL THEN NEW.reporter_phone := OLD.reporter_phone; END IF;
    IF OLD.reporter_student_id IS NOT NULL THEN NEW.reporter_student_id := OLD.reporter_student_id; END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS concerns_preserve_reporter_snapshot ON public.concerns;
CREATE TRIGGER concerns_preserve_reporter_snapshot
BEFORE UPDATE ON public.concerns
FOR EACH ROW EXECUTE FUNCTION public.preserve_concern_reporter_snapshot();

CREATE OR REPLACE FUNCTION public.preserve_report_reporter_snapshot()
RETURNS trigger AS $$
BEGIN
    IF OLD.reported_by_name IS NOT NULL THEN NEW.reported_by_name := OLD.reported_by_name; END IF;
    IF OLD.reporter_email IS NOT NULL THEN NEW.reporter_email := OLD.reporter_email; END IF;
    IF OLD.reporter_role IS NOT NULL THEN NEW.reporter_role := OLD.reporter_role; END IF;
    IF OLD.reporter_department IS NOT NULL THEN NEW.reporter_department := OLD.reporter_department; END IF;
    IF OLD.reporter_phone IS NOT NULL THEN NEW.reporter_phone := OLD.reporter_phone; END IF;
    IF OLD.reporter_student_id IS NOT NULL THEN NEW.reporter_student_id := OLD.reporter_student_id; END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS reports_preserve_reporter_snapshot ON public.reports;
CREATE TRIGGER reports_preserve_reporter_snapshot
BEFORE UPDATE ON public.reports
FOR EACH ROW EXECUTE FUNCTION public.preserve_report_reporter_snapshot();
SQL);
    }
};
