<?php

namespace App\Services;

class StudentMasterlistRows
{
    /** Map combined-name school masterlists to the legacy import layout. */
    public static function normalize(array $rows): array
    {
        $columns = null;
        $result = [['Student ID', 'Omega ID', 'Last Name', 'First Name', 'Middle Name', 'Program', 'Level']];
        foreach ($rows as $row) {
            $headers = array_map(fn ($value) => strtolower(trim((string) $value)), $row);
            if (in_array('student id', $headers, true) && in_array('student name', $headers, true)) {
                $columns = array_flip($headers);
                continue;
            }
            if ($columns === null) {
                continue;
            }
            $id = trim((string) ($row[$columns['student id']] ?? ''));
            if (! preg_match('/^\d{11}$/', $id)) {
                continue;
            }
            $name = trim((string) ($row[$columns['student name']] ?? ''));
            $parts = explode(',', $name, 2);
            $result[] = [$id, '', trim($parts[0]), trim($parts[1] ?? ''), '',
                trim((string) ($row[$columns['program'] ?? -1] ?? '')),
                trim((string) ($row[$columns['level'] ?? -1] ?? ''))];
        }

        return $columns === null ? $rows : $result;
    }
}
