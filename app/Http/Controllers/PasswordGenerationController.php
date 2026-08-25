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

        // Increase execution time for large batches
        set_time_limit(300); // 5 minutes max
        ini_set('memory_limit', '512M'); // Increase memory limit

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

            $userCount = $query->count();

            if ($userCount === 0) {
                return back()->with('warning', 'No users found matching the criteria.');
            }

            $successCount = 0;
            $failedCount = 0;
            $errors = [];
            
            // Process in chunks to avoid memory issues and handle large datasets
            $chunkSize = 500; // Process 500 users at a time
            $totalProcessed = 0;

            $query->chunk($chunkSize, function ($users) use (&$successCount, &$failedCount, &$errors, &$totalProcessed) {
                foreach ($users as $user) {
                    try {
                        // Generate new password
                        $newPassword = PasswordGenerator::generate(12);

                        // Update user password
                        $user->password = Hash::make($newPassword);
                        $user->force_password_change = false;
                        $user->save();

                        // Queue email notification instead of sending immediately
                        // This prevents timeout and handles failures gracefully
                        try {
                            $user->notify(new NewUserCreatedNotification($newPassword));
                            $successCount++;

                            Log::info('[PasswordGeneration] Password generated and email queued', [
                                'user_id' => $user->id,
                                'email' => $user->email,
                            ]);
                        } catch (\Exception $emailException) {
                            // Email failed but password was updated
                            Log::error('[PasswordGeneration] Failed to send email', [
                                'user_id' => $user->id,
                                'email' => $user->email,
                                'error' => $emailException->getMessage()
                            ]);
                            // Don't add to errors array to avoid memory issues with large datasets
                            $failedCount++;
                        }
                        
                        $totalProcessed++;
                    } catch (\Exception $e) {
                        Log::error('[PasswordGeneration] Failed to process user', [
                            'user_id' => $user->id ?? 'unknown',
                            'email' => $user->email ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                        $failedCount++;
                        $totalProcessed++;
                    }
                }
            });

            $message = "Password generation completed! ";
            $message .= "Total: {$userCount} | Successful: {$successCount} | Failed: {$failedCount}";

            if ($failedCount > 0) {
                return back()->with('warning', $message . ' Check logs for details on failed emails.');
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
