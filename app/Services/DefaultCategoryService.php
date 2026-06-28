<?php

namespace App\Services;

use App\Models\Category;

class DefaultCategoryService
{
    public static function ensureDefaults(): void
    {
        foreach (self::defaultCategories() as $categoryName => $issueProblemTypes) {
            $category = Category::whereRaw('LOWER(name) = ?', [strtolower($categoryName)])->first();

            if (! $category) {
                Category::create([
                    'name' => $categoryName,
                    'issues' => self::buildIssues($issueProblemTypes),
                ]);

                continue;
            }

            $category->update([
                'issues' => self::mergeIssues($category->issues, $issueProblemTypes),
            ]);
        }
    }

    private static function defaultCategories(): array
    {
        return [
            'Maintenance' => [
                'Aircon' => ['Not working', 'Weak cooling', 'Leaking', 'Noisy'],
                'Electric Outlet' => ['No power', 'Loose socket', 'Sparking', 'Damaged cover', 'Burnt smell'],
                'Room' => ['Needs cleaning', 'Damaged furniture', 'Bad odor', 'Leak', 'Lighting issue'],
                'Door' => ['Broken lock', 'Damaged handle', 'Hard to open/close', 'Loose hinge', 'Misaligned door'],
                'Window' => ['Broken glass', 'Stuck window', 'Loose frame', 'Leaking', 'Lock issue'],
            ],
            'Technology/Internet' => [
                'Internet Connection' => ['No connection', 'Slow connection', 'Intermittent connection', 'Wi-Fi not showing', 'Router/access point issue'],
                'Computer' => ['Not turning on', 'No display', 'Slow performance', 'Keyboard/mouse issue', 'Software issue'],
                'Printer' => ['Not printing', 'Paper jam', 'No ink/toner', 'Connection issue'],
                'Projector' => ['No display', 'Blurry display', 'No sound', 'Cable/HDMI issue', 'Remote/control issue'],
                'Network/System Access' => ['Cannot access system', 'Login issue', 'Account locked', 'LAN cable issue', 'Blocked website'],
            ],
        ];
    }

    private static function buildIssues(array $issueProblemTypes): array
    {
        return collect($issueProblemTypes)
            ->map(fn (array $problemTypes, string $name) => [
                'name' => $name,
                'problem_types' => $problemTypes,
            ])
            ->values()
            ->all();
    }

    private static function mergeIssues(mixed $existingIssues, array $issueProblemTypes): array
    {
        $issues = is_array($existingIssues) ? $existingIssues : [];
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
                if ($name === '') {
                    return null;
                }

                $matchingIssueName = collect(array_keys($issueProblemTypes))
                    ->first(fn ($defaultName) => strtolower($defaultName) === strtolower($name));

                $problemTypes = is_array($issue['problem_types'] ?? null) ? $issue['problem_types'] : [];
                if ($matchingIssueName !== null) {
                    $foundIssues[] = strtolower($matchingIssueName);
                    $problemTypes = collect($problemTypes)
                        ->merge($issueProblemTypes[$matchingIssueName])
                        ->map(fn ($problemType) => trim((string) $problemType))
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                }

                return [
                    'name' => $name,
                    'problem_types' => $problemTypes,
                ];
            })
            ->filter()
            ->values()
            ->all();

        foreach ($issueProblemTypes as $issueName => $problemTypes) {
            if (in_array(strtolower($issueName), $foundIssues, true)) {
                continue;
            }

            $issues[] = [
                'name' => $issueName,
                'problem_types' => $problemTypes,
            ];
        }

        return $issues;
    }
}
