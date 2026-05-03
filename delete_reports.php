<?php
/**
 * Script to delete specific reports from database
 * Run with: php artisan tinker < delete_reports.php
 */

// Delete reports by title
$reports = \App\Models\Report::whereIn('title', ['Door', 'Electrical outlet'])->get();

foreach ($reports as $report) {
    echo "Deleting Report ID: {$report->id} - Title: {$report->title}\n";
    $report->forceDelete(); // Permanently delete
}

echo "Done! Deleted " . count($reports) . " report(s).\n";
