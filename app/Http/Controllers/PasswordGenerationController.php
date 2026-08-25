<?php

namespace App\Http\Controllers;

use App\Helpers\PasswordGenerator;
use App\Models\User;
use App\Notifications\NewUserCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordGenerationController extends Controller
{
    public function showForm()
    {
        // Only superadmin or MIS can access
        if (!auth()->check() || !in_array(auth()->user()->role, ['superadmin', 'mis'])) {
            abort(403, 'Unauthorized access.');
        }

        return view('admin.generate-passwords');
    }

    public function generate(Request $request)
    {
        // Only superadmin or MIS can access
        if (!auth()->check() || !in_array(auth()->user()->role, ['superadmin', 'mis'])) {
            abort(403, 'Unauthorized access.');
        }

        try {
            $request->validate([
                'scope' => 'required|in:all,role,email',
                'role' => 'nullable|in:student,faculty,maintenance,mis,school_admin,building_admin,academic_head,program_head,principal_assistant',
                'email' => 'nullable|email',
                'exclude_superadmin' => 'nullable|boolean',
            ]);

            // Build query
            $query = User::query();

            // Exclude superadmin if requested
            if ($request->boolean('exclude_superadmin')) {
                // Check if column exists first
                if (\Schema::hasColumn('users', 'is_superadmin')) {
                    $query->where('is_superadmin', false);
                }
                $query->where('role', '!=', 'superadmin');
            }

            if ($request->scope === 'email' && $request->filled('email')) {
                $query->where('email', $request->email);
            } elseif ($request->scope === 'role' && $request->filled('role')) {
                $query->where('role', $request->role);
            } elseif ($request->scope !== 'all') {
                return back()->with('error', 'Please provide required parameters for the selected scope.');
            }

            $users = $query->get();

            if ($users->isEmpty()) {
                return back()->with('warning', 'No users found matching the criteria.');
            }

            // Limit to prevent timeout on Vercel (max 100 users at a time)
            if ($users->count() > 100) {
                return back()->with('error', 'Too many users selected (' . $users->count() . '). Please select a maximum of 100 users at a time, or filter by role/email.');
            }

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($users as $user) {
                try {
                    // Generate new password
                    $newPassword = PasswordGenerator::generate(12);

                    // Update user password
                    $user->password = Hash::make($newPassword);
                    $user->force_password_change = false;
                    $user->save();

                    // Send email notification
                    try {
                        $user->notify(new NewUserCreatedNotification($newPassword));
                        $successCount++;

                        Log::info('[PasswordGeneration] Password generated and email sent', [
                            'user_id' => $user->id,
                            'email' => $user->email,
                        ]);
                    } catch (\Exception $emailException) {
                        Log::error('[PasswordGeneration] Failed to send email', [
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'error' => $emailException->getMessage()
                        ]);
                        $errors[] = "Failed to send email to {$user->email}: " . $emailException->getMessage();
                        $failedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('[PasswordGeneration] Failed to process user', [
                        'user_id' => $user->id ?? 'unknown',
                        'email' => $user->email ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    $errors[] = "Failed to process user {$user->email}: " . $e->getMessage();
                    $failedCount++;
                }
            }

            $message = "Password generation completed! ";
            $message .= "Total: {$users->count()} | Successful: {$successCount} | Failed: {$failedCount}";

            if ($failedCount > 0) {
                return back()->with('warning', $message)->with('errors', $errors);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('[PasswordGeneration] Critical error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
