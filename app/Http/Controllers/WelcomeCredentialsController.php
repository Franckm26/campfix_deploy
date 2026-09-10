<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WelcomeEmailDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class WelcomeCredentialsController extends Controller
{
    /**
     * Show the welcome credentials page
     */
    public function index()
    {
        return view('auth.welcome-credentials');
    }

    /**
     * Get user credentials by student ID
     */
    public function getCredentials($studentId)
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

            // Check if there's a welcome email delivery record with encrypted password
            $delivery = WelcomeEmailDelivery::where('user_id', $user->id)
                ->whereNotNull('encrypted_password')
                ->first();

            if (!$delivery || !$delivery->encrypted_password) {
                return response()->json([
                    'success' => false,
                    'message' => 'No temporary password available for this account'
                ], 404);
            }

            // Decrypt the password
            $password = Crypt::decryptString($delivery->encrypted_password);

            return response()->json([
                'success' => true,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $password,
                    'student_id' => $user->student_id
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching welcome credentials: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching credentials'
            ], 500);
        }
    }
}
