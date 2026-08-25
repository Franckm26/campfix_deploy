<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmergencyRecoveryController extends Controller
{
    public function showForm()
    {
        return view('emergency.recovery');
    }

    public function unlockAndReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|string|min:8',
            'emergency_key' => 'required|string'
        ]);

        // Simple emergency key check (you can change this)
        $validKeys = [
            'CAMPFIX_EMERGENCY_2026',
            'FRANCK_RECOVERY_KEY',
            'ADMIN_UNLOCK_ACCESS'
        ];

        if (!in_array($request->emergency_key, $validKeys)) {
            return back()->with('error', 'Invalid emergency key provided.');
        }

        try {
            // Find user by email (including locked superadmin accounts)
            $user = User::withoutGlobalScopes()->where('email', $request->email)->first();

            if (!$user) {
                return back()->with('error', 'User not found with email: ' . $request->email);
            }

            $wasLocked = $user->locked_until && $user->locked_until > now();

            // Unlock and reset password
            $user->update([
                'password' => Hash::make($request->new_password),
                'locked_until' => null,
                'failed_login_attempts' => 0,
                'login_lockout_level' => 0,
                'force_password_change' => false,
            ]);

            // Log the recovery action
            \App\Models\ActivityLog::create([
                'user_id' => null, // No authenticated user for emergency recovery
                'action' => 'emergency_account_recovery',
                'description' => "Emergency recovery for user: {$user->name} ({$user->email})",
                'model_type' => 'user',
                'model_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $message = "✅ Emergency recovery completed for '{$user->name}'!\n\n";
            $message .= "📧 Email: {$user->email}\n";
            $message .= "🔑 New Password: {$request->new_password}\n";
            $message .= "🔓 Status: " . ($wasLocked ? "Unlocked" : "Password Reset") . "\n\n";
            $message .= "You can now login with these credentials.";

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Recovery failed: ' . $e->getMessage());
        }
    }
}