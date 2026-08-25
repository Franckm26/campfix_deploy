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

        // Get recently reset passwords (last 100)
        $recentResets = User::whereNotNull('password_reset_at')
            ->orderBy('password_reset_at', 'desc')
            ->limit(100)
            ->get(['id', 'name', 'email', 'role', 'password_reset_at']);

        // Get count of users with/without reset
        $totalUsers = User::count();
        $resetUsers = User::whereNotNull('password_reset_at')->count();
        $unresetUsers = $totalUsers - $resetUsers;

        return view('admin.generate-passwords', compact('recentResets', 'totalUsers', 'resetUsers', 'unresetUsers'));
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
        
        // Force synchronous email sending (don't queue)
        config(['queue.default' => 'sync']);

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
            $emailErrors = []; // Track first 10 email errors for display

            $query->chunk($chunkSize, function ($users) use (&$successCount, &$failedCount, &$errors, &$totalProcessed, &$emailErrors) {
                foreach ($users as $user) {
                    try {
                        // Generate new password
                        $newPassword = PasswordGenerator::generate(12);

                        // Update user password
                        $user->password = Hash::make($newPassword);
                        $user->force_password_change = false;
                        $user->password_reset_at = now(); // Track when password was reset
                        $user->save();

                        // Send email notification immediately (sync mode set at method start)
                        try {
                            $user->notify(new NewUserCreatedNotification($newPassword));
                            $successCount++;

                            Log::info('[PasswordGeneration] Password generated and email sent', [
                                'user_id' => $user->id,
                                'email' => $user->email,
                            ]);
                        } catch (\Exception $emailException) {
                            // Email failed but password was updated
                            Log::error('[PasswordGeneration] Failed to send email', [
                                'user_id' => $user->id,
                                'email' => $user->email,
                                'error' => $emailException->getMessage(),
                                'trace' => $emailException->getTraceAsString()
                            ]);
                            
                            // Store first 10 errors for display
                            if (count($emailErrors) < 10) {
                                $emailErrors[] = "Email to {$user->email} failed: " . $emailException->getMessage();
                            }
                            
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
                $warningMessage = $message;
                if (!empty($emailErrors)) {
                    $warningMessage .= "\n\nSample email errors (first 10):";
                }
                return back()
                    ->with('warning', $warningMessage)
                    ->with('errors', $emailErrors);
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
