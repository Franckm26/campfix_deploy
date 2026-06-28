<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $category = DB::table('categories')
            ->whereRaw('LOWER(name) = ?', ['maintenance'])
            ->first();

        if (! $category) {
            return;
        }

        $issues = json_decode($category->issues ?? '[]', true);
        $issues = is_array($issues) ? $issues : [];
        $issueProblemTypes = [
            'aircon' => ['Not working', 'Weak cooling', 'Leaking', 'Noisy'],
            'electric outlet' => ['No power', 'Loose socket', 'Sparking', 'Damaged cover', 'Burnt smell'],
            'room' => ['Needs cleaning', 'Damaged furniture', 'Bad odor', 'Leak', 'Lighting issue'],
            'door' => ['Broken lock', 'Damaged handle', 'Hard to open/close', 'Loose hinge', 'Misaligned door'],
            'window' => ['Broken glass', 'Stuck window', 'Loose frame', 'Leaking', 'Lock issue'],
        ];
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
                'name' => ucwords($issueName),
                'problem_types' => $problemTypes,
            ];
        }

        DB::table('categories')
            ->where('id', $category->id)
            ->update(['issues' => json_encode($issues)]);
    }

    public function down(): void
    {
        $category = DB::table('categories')
            ->whereRaw('LOWER(name) = ?', ['maintenance'])
            ->first();

        if (! $category) {
            return;
        }

        $issues = json_decode($category->issues ?? '[]', true);
        $issues = is_array($issues) ? $issues : [];
        $issueProblemTypes = [
            'aircon' => ['Not working', 'Weak cooling', 'Leaking', 'Noisy'],
            'electric outlet' => ['No power', 'Loose socket', 'Sparking', 'Damaged cover', 'Burnt smell'],
            'room' => ['Needs cleaning', 'Damaged furniture', 'Bad odor', 'Leak', 'Lighting issue'],
            'door' => ['Broken lock', 'Damaged handle', 'Hard to open/close', 'Loose hinge', 'Misaligned door'],
            'window' => ['Broken glass', 'Stuck window', 'Loose frame', 'Leaking', 'Lock issue'],
        ];

        $issues = collect($issues)
            ->map(function ($issue) use ($issueProblemTypes) {
                if (is_string($issue)) {
                    return $issue;
                }

                $issueKey = strtolower((string) ($issue['name'] ?? ''));
                if (! isset($issueProblemTypes[$issueKey])) {
                    return $issue;
                }

                $issue['problem_types'] = collect($issue['problem_types'] ?? [])
                    ->reject(fn ($problemType) => in_array($problemType, $issueProblemTypes[$issueKey], true))
                    ->values()
                    ->all();

                return $issue;
            })
            ->values()
            ->all();

        DB::table('categories')
            ->where('id', $category->id)
            ->update(['issues' => json_encode($issues)]);
    }
};
