<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $category = DB::table('categories')
            ->whereRaw('LOWER(name) = ?', ['technology/internet'])
            ->first();

        if (! $category) {
            DB::table('categories')->insert([
                'name' => 'Technology/Internet',
                'issues' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $category = DB::table('categories')
                ->whereRaw('LOWER(name) = ?', ['technology/internet'])
                ->first();
        }

        if (! $category) {
            return;
        }

        $issues = $this->mergeIssues($category->issues ?? '[]');

        DB::table('categories')
            ->where('id', $category->id)
            ->update([
                'issues' => json_encode($issues),
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        $category = DB::table('categories')
            ->whereRaw('LOWER(name) = ?', ['technology/internet'])
            ->first();

        if (! $category) {
            return;
        }

        $seededProblemTypes = $this->issueProblemTypes();
        $issues = json_decode($category->issues ?? '[]', true);
        $issues = is_array($issues) ? $issues : [];

        $issues = collect($issues)
            ->map(function ($issue) use ($seededProblemTypes) {
                if (is_string($issue)) {
                    return $issue;
                }

                $issueKey = strtolower((string) ($issue['name'] ?? ''));
                if (! isset($seededProblemTypes[$issueKey])) {
                    return $issue;
                }

                $issue['problem_types'] = collect($issue['problem_types'] ?? [])
                    ->reject(fn ($problemType) => in_array($problemType, $seededProblemTypes[$issueKey], true))
                    ->values()
                    ->all();

                return $issue;
            })
            ->values()
            ->all();

        DB::table('categories')
            ->where('id', $category->id)
            ->update([
                'issues' => json_encode($issues),
                'updated_at' => now(),
            ]);
    }

    private function mergeIssues(?string $existingIssues): array
    {
        $issues = json_decode($existingIssues ?? '[]', true);
        $issues = is_array($issues) ? $issues : [];
        $issueProblemTypes = $this->issueProblemTypes();
        $foundIssues = [];

        $issues = collect($issues)
            ->map(function ($issue) use ($issueProblemTypes, &$foundIssues) {
                if (is_string($issue)) {
                    $issue = [
                        'name' => $issue,
                        'problem_types' => [],
                    ];
                }

                $name = trim((string) ($issue['name'] ?? ''));
                $existingProblemTypes = is_array($issue['problem_types'] ?? null) ? $issue['problem_types'] : [];
                $issueKey = strtolower($name);

                if (isset($issueProblemTypes[$issueKey])) {
                    $foundIssues[] = $issueKey;
                    $existingProblemTypes = collect($existingProblemTypes)
                        ->merge($issueProblemTypes[$issueKey])
                        ->map(fn ($problemType) => trim((string) $problemType))
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                }

                return [
                    'name' => $name,
                    'problem_types' => $existingProblemTypes,
                ];
            })
            ->filter(fn ($issue) => $issue['name'] !== '')
            ->values()
            ->all();

        foreach ($issueProblemTypes as $issueName => $problemTypes) {
            if (in_array($issueName, $foundIssues, true)) {
                continue;
            }

            $issues[] = [
                'name' => $this->displayIssueName($issueName),
                'problem_types' => $problemTypes,
            ];
        }

        return $issues;
    }

    private function issueProblemTypes(): array
    {
        return [
            'internet connection' => ['No connection', 'Slow connection', 'Intermittent connection', 'Wi-Fi not showing', 'Router/access point issue'],
            'computer' => ['Not turning on', 'No display', 'Slow performance', 'Keyboard/mouse issue', 'Software issue'],
            'printer' => ['Not printing', 'Paper jam', 'No ink/toner', 'Connection issue'],
            'projector' => ['No display', 'Blurry display', 'No sound', 'Cable/HDMI issue', 'Remote/control issue'],
            'network/system access' => ['Cannot access system', 'Login issue', 'Account locked', 'LAN cable issue', 'Blocked website'],
        ];
    }

    private function displayIssueName(string $issueName): string
    {
        return match ($issueName) {
            'network/system access' => 'Network/System Access',
            default => ucwords($issueName),
        };
    }
};
