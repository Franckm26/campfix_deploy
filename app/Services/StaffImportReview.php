<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StaffImportReview
{
    public const DEPARTMENTS = ['GE', 'ICT', 'Business Management', 'THM'];

    public const ROLES = [
        'faculty' => 'Faculty / Other Staff', 'maintenance' => 'Maintenance', 'mis' => 'MIS',
        'school_admin' => 'School Administrator', 'building_admin' => 'Building Administrator',
        'academic_head' => 'Academic Head', 'program_head' => 'Program Head', 'principal_assistant' => 'Principal Assistant',
    ];

    public function parse(UploadedFile $file, string $format): array
    {
        $tabSeparated = $format === 'masterlist';
        if (strtolower($file->getClientOriginalExtension()) !== 'xlsx') {
            $header = strtok(file_get_contents($file->getRealPath()), "\r\n") ?: '';
            $tabSeparated = substr_count($header, "\t") > substr_count($header, ',');
        }
        $rows = app(UserImportFile::class)->rows($file, $tabSeparated, false);
        $headers = array_map(fn ($value) => strtolower(trim((string) $value, " \t\n\r\0\x0B\xEF\xBB\xBF")), $rows[0] ?? []);
        $nameIndex = array_search('name', $headers, true);
        $emailIndex = array_search('email', $headers, true);
        $roleIndex = array_search('role', $headers, true);
        $result = [];
        foreach (array_slice($rows, 1, null, true) as $index => $row) {
            $row = array_map(fn ($value) => trim((string) $value), $row);
            if (! array_filter($row, fn ($value) => $value !== '')) continue;
            if ($nameIndex !== false && $emailIndex !== false) {
                $name = $row[$nameIndex] ?? ''; $email = $row[$emailIndex] ?? '';
                $role = $roleIndex !== false ? ($row[$roleIndex] ?? '') : '';
                $employee = null; $password = Str::password(16);
            } elseif ($format === 'masterlist' && count($row) >= 6) {
                $name = trim(($row[2] ?? '').' '.($row[3] ?? '').' '.($row[1] ?? ''));
                if (blank($row[1] ?? null) || blank($row[2] ?? null)) continue;
                $first = strtolower(preg_replace('/[^a-zA-Z]/', '', $row[2]));
                $last = strtolower(preg_replace('/[^a-zA-Z]/', '', $row[1]));
                $email = $first.'.'.$last.'@novaliches.sti.edu.ph';
                $employee = preg_replace('/\s+/', '', $row[5]) ?: null;
                $password = ucfirst($last).'_'.ucfirst($first).'_'.now()->year;
                $role = $row[6] ?? '';
            } elseif ($format === 'standard' && count($row) >= 3) {
                $employee = $row[0] ?: null; $name = $row[0]; $email = $row[1];
                $password = $row[2] ?: Str::password(16); $role = $row[3] ?? '';
            } else {
                continue;
            }
            $role = strtolower(str_replace([' ', '-'], '_', trim($role)));
            $role = ['school_administrator' => 'school_admin', 'building_administrator' => 'building_admin',
                'laboratory_custodian' => 'maintenance'][$role] ?? $role;
            if (($employee !== null && mb_strlen($employee) > 255) || mb_strlen($password) > 1000) {
                throw ValidationException::withMessages(['file' => 'Row '.($index + 1).' contains an employee ID or password that exceeds the allowed length.']);
            }
            $result[] = ['row_id' => (string) $index, 'name' => $name, 'email' => strtolower($email),
                'role' => array_key_exists($role, self::ROLES) ? $role : 'faculty',
                'employee_id' => $employee, 'password' => $password];
        }
        if (! $result) throw ValidationException::withMessages(['file' => 'No staff users were found. Upload a staff masterlist or a CSV with Name, Email, Role columns.']);
        return $result;
    }
}
