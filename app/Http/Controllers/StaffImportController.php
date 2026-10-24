<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\StaffImportReview;
use App\Services\UserImportFile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StaffImportController extends Controller
{
    public function preview(Request $request)
    {
        abort_unless(auth()->user()->canAccess('users_create'), 403);
        $request->validate(['file' => UserImportFile::rules(), 'file_format' => 'required|in:masterlist,standard']);
        $rows = app(StaffImportReview::class)->parse($request->file('file'), $request->input('file_format'));
        $token = (string) Str::uuid();
        $request->session()->put('staff_import_review', ['token' => $token, 'user_id' => auth()->id(),
            'expires_at' => now()->addMinutes(30)->timestamp, 'rows' => $rows]);
        $emails = User::withoutGlobalScopes()->pluck('email')->map(fn ($email) => strtolower($email))->all();
        return response()->json(['success' => true, 'token' => $token, 'roles' => StaffImportReview::ROLES,
            'departments' => StaffImportReview::DEPARTMENTS,
            'rows' => array_map(fn ($row) => array_merge(array_intersect_key($row, array_flip(['row_id', 'name', 'email', 'role'])),
                ['warning' => in_array($row['email'], $emails, true) ? 'Email already exists. Change it or remove this row.' : null]), $rows)]);
    }

    public function confirm(Request $request)
    {
        abort_unless(auth()->user()->canAccess('users_create'), 403);
        $request->validate(['preview_token' => 'required|uuid']);
        $draft = $request->session()->get('staff_import_review');
        abort_unless($draft && (int) $draft['user_id'] === (int) auth()->id()
            && hash_equals($draft['token'], (string) $request->input('preview_token'))
            && $draft['expires_at'] >= now()->timestamp, 422, 'This staff preview expired or was already imported. Upload the file again.');
        $rows = $request->input('rows');
        if (is_array($rows)) {
            foreach ($rows as &$row) {
                if (!is_array($row)) continue;
                foreach (['name', 'email', 'department'] as $key) if (is_string($row[$key] ?? null)) $row[$key] = trim($row[$key]);
                if (is_string($row['email'] ?? null)) $row['email'] = strtolower($row['email']);
            }
            unset($row);
            $request->merge(['rows' => $rows]);
        }
        $data = $request->validate([
            'rows' => 'required|array|min:1|max:9999', 'rows.*' => 'required|array:row_id,name,email,role,department',
            'rows.*.row_id' => 'required|string|distinct', 'rows.*.name' => 'required|string|max:255',
            'rows.*.email' => 'required|email|max:255|distinct:ignore_case',
            'rows.*.role' => ['required', Rule::in(array_keys(StaffImportReview::ROLES))],
            'rows.*.department' => ['required_if:rows.*.role,program_head', 'nullable', Rule::in(StaffImportReview::DEPARTMENTS)],
            'archive_folder_name' => 'nullable|string|max:255',
        ]);
        $original = collect($draft['rows'])->keyBy('row_id');
        $existing = User::withoutGlobalScopes()->pluck('email')->map(fn ($email) => strtolower($email))->all();
        $ids = User::withoutGlobalScopes()->whereNotNull('student_id')->pluck('student_id')->map(fn ($id) => strtolower(trim($id)))->all();
        $reviewed = [];
        foreach ($data['rows'] as $index => $row) {
            $source = $original->get($row['row_id']);
            if (!$source) throw ValidationException::withMessages(['rows.'.$index.'.name' => 'This row does not belong to the uploaded file.']);
            if (in_array($row['email'], $existing, true)) throw ValidationException::withMessages(['rows.'.$index.'.email' => 'This email is already used by an account.']);
            if (filled($source['employee_id']) && in_array(strtolower($source['employee_id']), $ids, true)) {
                throw ValidationException::withMessages(['rows.'.$index.'.name' => 'This employee ID already exists. Remove this row from the import.']);
            }
            $reviewed[] = [$source['employee_id'], $row['email'], $source['password'], $row['role'], $row['name'],
                $row['role'] === 'program_head' ? $row['department'] : null];
            if (filled($source['employee_id'])) $ids[] = strtolower($source['employee_id']);
        }
        $request->merge(['default_role' => 'staff', 'file_format' => 'standard']);
        try {
            $response = app(AdminController::class)->importReviewedStaff($request, $reviewed);
        } catch (\Illuminate\Database\QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                throw ValidationException::withMessages(['rows' => 'An account was created with one of these emails or employee IDs. Review the list and try again.']);
            }
            throw $exception;
        }
        $request->session()->forget('staff_import_review');
        return $response;
    }
}
