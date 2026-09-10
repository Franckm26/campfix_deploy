<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WelcomeEmailDelivery;
use App\Notifications\EmailAddressNotification;
use App\Notifications\PasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class WelcomeCredentialsController extends Controller
{
    /**
     * Show the welcome credentials page with all students
     */
    public function index()
    {
        // Get all users with their welcome email delivery status
        $students = User::leftJoin('welcome_email_deliveries', 'users.id', '=', 'welcome_email_deliveries.user_id')
            ->whereNotNull('users.student_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.student_id',
                'users.role',
                'welcome_email_deliveries.status',
                'welcome_email_deliveries.sent_at',
                'welcome_email_deliveries.created_at as queued_at',
                'welcome_email_deliveries.encrypted_password'
            )
            ->orderBy('users.name', 'asc')
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'student_id' => $student->student_id,
                    'role' => $student->role,
                    'status' => $student->status ?? 'pending',
                    'sent_at' => $student->sent_at,
                    'has_credentials' => !empty($student->encrypted_password)
                ];
            });

        return view('auth.welcome-credentials', compact('students'));
    }

    /**
     * Search for user by student ID (don't show password)
     */
    public function search($studentId)
    {
        try {
            // Find user by student_id
            $user = User::where('student_id', $studentId)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID not found'
                ], 404);
            }

            // Check if there's a welcome email delivery record
            $delivery = WelcomeEmailDelivery::where('user_id', $user->id)
                ->whereNotNull('encrypted_password')
                ->first();

            if (!$delivery || !$delivery->encrypted_password) {
                return response()->json([
                    'success' => false,
                    'message' => 'No credentials available for this account'
                ], 404);
            }

            // Return user info without password
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'student_id' => $user->student_id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching'
            ], 500);
        }
    }

    /**
     * Send credentials to user's email
     */
    public function send($userId)
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Get the encrypted password
            $delivery = WelcomeEmailDelivery::where('user_id', $user->id)
                ->whereNotNull('encrypted_password')
                ->first();

            if (!$delivery || !$delivery->encrypted_password) {
                return response()->json([
                    'success' => false,
                    'message' => 'No credentials available'
                ], 404);
            }

            // Decrypt password
            $password = Crypt::decryptString($delivery->encrypted_password);

            // Send email address notification
            $user->notify(new EmailAddressNotification());

            // Send password notification
            $user->notify(new PasswordNotification($password));

            // Update delivery record
            $delivery->update([
                'email_address_sent_at' => now(),
                'password_sent_at' => now(),
                'sent_at' => now(),
                'status' => 'sent'
            ]);

            Log::info('Welcome credentials sent via web page', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Credentials sent successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending credentials: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send credentials'
            ], 500);
        }
    }
}
