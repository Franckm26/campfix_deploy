<?php

namespace App\Http\Controllers;

use App\Mail\SendOtpMail;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\LoginLockedNotification;
use App\Notifications\SuspiciousLoginNotification;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected int $apiLockoutThreshold = 5;

    protected int $apiLockoutMinutes = 15;

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // OWASP A2: Strong password policy
        $request->validate([
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                // OWASP A2: Prevent common passwords
                'not_in:password,12345678,123456789,qwerty,admin123,letmein,welcome',
            ],
        ], [
            'password.regex' => 'Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.',
            'password.not_in' => 'This password is too common. Please choose a stronger password.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect('/')->with('success', 'Account created successfully! Please login.');
    }

    public function login(Request $request)
    {
        // OWASP A2: Rate limiting is handled by middleware
        // Validate login input
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|min:1',
        ]);


        $user = User::where('email', $request->email)->first();

        // Check if account is locked — only MIS can unlock
        if ($user && $user->locked_until) {
            return back()->with('error', 'Your account has been locked due to too many failed login attempts. Please contact the MIS administrator to unlock your account.');
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            // Check if user is archived
            if ($user->is_archived || $user->archive_folder_id) {
                Auth::logout();

                return back()->with('error', 'Your account has been archived and cannot login.');
            }

            // Reset failed login attempts on successful login
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'login_lockout_level' => 0,
            ]);

            // Generate secure OTP using random_int for better security
            $otp = (string) random_int(100000, 999999);

            $user->update([
                'otp' => \Illuminate\Support\Facades\Hash::make($otp),
                'otp_expires_at' => now()->utc()->addMinutes(5),
                'otp_attempts' => 0,
            ]);

            // Save user id for verification and include phone info
            session([
                'otp_user' => $user->id,
                'otp_email' => $user->email,
                'otp_phone' => $user->phone ?? 'your phone number',
            ]);

            // Logout until OTP verified
            Auth::logout();

            // Redirect to choose OTP delivery method
            return redirect('/otp-choice')->with('success', 'Choose how to receive your OTP.');
        }

        // Handle failed login attempts — lock permanently after 3 failures
        if ($user) {
            $attempts = $user->failed_login_attempts + 1;

            if ($attempts >= 3) {
                $user->update([
                    'failed_login_attempts' => $attempts,
                    'locked_until' => now()->addYears(100), // effectively permanent
                    'login_lockout_level' => 1,
                ]);

                ActivityLog::log(
                    'account_locked',
                    "Account {$user->email} permanently locked after {$attempts} failed login attempts from IP ".$request->ip(),
                    $user->id,
                    'user'
                );

                $this->notifyMisOfSuspiciousLogin($user->email, $attempts, $request->ip(), 'Account permanently locked. MIS must unlock.');

                try {
                    $user->notify(new LoginLockedNotification(
                        $attempts,
                        $request->ip(),
                        'indefinitely',
                        'until unlocked by MIS'
                    ));
                } catch (\Exception $e) {
                    \Log::error('Failed to send lockout email: ' . $e->getMessage());
                }

                return back()->with('error', 'Your account has been locked due to too many failed login attempts. Please contact the MIS administrator to unlock your account.');
            }

            $user->update(['failed_login_attempts' => $attempts]);
        }

        return back()->with('error', 'Invalid email or password');
    }

    public function verifyOtp(Request $request)
    {

        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = User::find(session('otp_user'));

        if (! $user) {
            return redirect('/')->with('error', 'Session expired.');
        }

        // Brute-force protection: max 5 attempts per OTP
        $attempts = (int) ($user->otp_attempts ?? 0);
        if ($attempts >= 5) {
            $user->update(['otp' => null, 'otp_expires_at' => null, 'otp_attempts' => 0]);
            return redirect('/')->with('error', 'Too many incorrect attempts. Please login again.');
        }

        // Manual UTC timestamp comparison to avoid casting issues
        $expiresAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $user->otp_expires_at->format('Y-m-d H:i:s'), 'UTC');
        $currentTime = now()->utc();
        if (\Illuminate\Support\Facades\Hash::check($request->otp, $user->otp) && $expiresAt->gt($currentTime)) {
            Auth::login($user);

            // ── Single-session enforcement ──────────────────────────────
            // If the user already has an active session on another device,
            // delete it from the sessions table so they get kicked out.
            if ($user->active_session_id && $user->active_session_id !== session()->getId()) {
                \DB::table('sessions')->where('id', $user->active_session_id)->delete();
            }

            // Store the new session ID on the user record
            $user->active_session_id = session()->getId();
            // ────────────────────────────────────────────────────────────

            $user->update([
                'otp' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
                'active_session_id' => session()->getId(),
            ]);

            // Clear rate limits after successful login
            \App\Http\Middleware\RateLimitMiddleware::clearRateLimit('login:' . $request->ip());
            if ($user->email) {
                \App\Http\Middleware\RateLimitMiddleware::clearRateLimit('login:' . strtolower($user->email));
            }

            // Check if user needs to change password on first login
            if ($user->force_password_change) {
                return redirect('/first-login-password')->with('info', 'Please set your new password and contact number.');
            }

            if ($user->role == 'mis') {
                return redirect('/admin');
            }

            return redirect('/dashboard');
        }

        $user->increment('otp_attempts');
        return back()->with('error', 'Invalid or expired OTP');
    }

    // Handle OTP delivery choice and send OTP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'delivery_method' => 'required|in:email,phone',
        ]);

        $userId = session('otp_user');

        if (! $userId) {
            return redirect('/')->with('error', 'Session expired. Please login again.');
        }

        $user = User::find($userId);

        if (! $user) {
            return redirect('/')->with('error', 'User not found.');
        }

        $deliveryMethod = $request->delivery_method;

        // Check if phone is available when phone method is selected
        if ($deliveryMethod === 'phone' && empty($user->phone)) {
            return back()->with('error', 'No phone number on file. Please select email instead.');
        }

        // Generate secure OTP using random_int
        $otp = (string) random_int(100000, 999999);

        $user->update([
            'otp' => \Illuminate\Support\Facades\Hash::make($otp),
            'otp_expires_at' => now()->utc()->addMinutes(5),
            'otp_attempts' => 0,
        ]);

        $destination = '';

        if ($deliveryMethod === 'email') {
            // Send via email immediately (not queued to avoid queue issues)
            try {
                Mail::to($user->email)->send(new SendOtpMail($otp));
                $destination = $user->email;
                \Log::info('OTP email sent successfully', ['email' => $user->email, 'user_id' => $user->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to send OTP email: ' . $e->getMessage(), ['email' => $user->email, 'user_id' => $user->id]);
                return back()->with('error', 'Failed to send email. Please try again or choose SMS instead.');
            }
        } elseif ($deliveryMethod === 'phone') {
            // Send via SMS using the SMS API service
            $destination = $user->phone;

            // Use the SMS service to send OTP
            $smsService = new SmsService;
            $smsSent = $smsService->sendOtp($user->phone, (string) $otp);

            if (! $smsSent) {
                return back()->with('error', 'Failed to send SMS. Please try again or choose email instead.');
            }
        }

        // Store delivery info for the verify page
        session(['otp_delivery' => $deliveryMethod, 'otp_destination' => $destination]);

        return redirect('/verify-otp')->with('success', "OTP sent to your {$deliveryMethod}.");
    }

    public function resendOtp()
    {
        $userId = session('otp_user');

        if (! $userId) {
            return redirect('/')->with('error', 'Session expired. Please login again.');
        }

        $user = User::find($userId);

        if (! $user) {
            return redirect('/')->with('error', 'User not found.');
        }

        // Instead of auto-resending, redirect to choice page
        return redirect('/otp-choice')->with('info', 'Choose how to receive your OTP.');
    }

    /**
     * Verify the current user's password for sensitive module access
     */
    public function verifyAccessPassword(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        if (Hash::check($request->password, auth()->user()->password)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Incorrect password.'], 401);
    }

    /**
     * Notify all MIS users of suspicious login activity
     */
    private function notifyMisOfSuspiciousLogin(string $email, int $attempts, string $ip, string $lockoutInfo): void
    {
        try {
            $misUsers = User::where('role', 'mis')->get();
            foreach ($misUsers as $misUser) {
                $misUser->notify(new SuspiciousLoginNotification($email, $attempts, $ip, $lockoutInfo));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to notify MIS of suspicious login: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        $user = Auth::user();

        // Clear the stored session ID so the slot is freed
        if ($user) {
            $user->update(['active_session_id' => null]);
        }

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully');
    }

    // Show first login password change form
    public function showFirstLoginPassword()
    {
        $user = auth()->user();

        // Only allow access if force_password_change is true
        if (! $user->force_password_change) {
            return redirect('/dashboard');
        }

        return view('auth.first-login-password');
    }

    // Process first login password change
    public function updateFirstLoginPassword(Request $request)
    {
        $user = auth()->user();

        // Only allow if force_password_change is true
        if (! $user->force_password_change) {
            return redirect('/dashboard');
        }

        // OWASP A2: Strong password requirements
        $request->validate([
            'name' => 'required|min:2|max:255',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'max:20',
                'regex:/^\S+$/',           // no spaces
                'regex:/[A-Z]/',           // at least one uppercase
                'regex:/[0-9]/',           // at least one number
            ],
            'phone' => 'required|regex:/^09[0-9]{9}$/',
        ], [
            'password.min'   => 'Password must be at least 8 characters.',
            'password.max'   => 'Password must not exceed 20 characters.',
            'password.regex' => 'Password must contain at least one uppercase letter, one number, and no spaces.',
            'phone.regex'    => 'Please enter a valid 11-digit Philippine mobile number (e.g., 09123456789)',
        ]);

        $user->update([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'force_password_change' => false,
        ]);

        return redirect('/dashboard')->with('success', 'Profile updated successfully!');
    }

    // ============ API METHODS (JWT) ============

    /**
     * API Login - Returns JWT token
     * OWASP A2: Rate limiting handled by middleware, Account lockout implemented here
     */
    public function apiLogin(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email:rfc,dns|max:255',
            'password' => 'required|string|min:8|max:255',
        ]);

        $email = strtolower(trim($validated['email']));
        $password = $validated['password'];

        if (! $this->hasAllowedEmailDomain($email)) {
            return response()->json([
                'message' => 'Invalid login request.',
                'errors' => [
                    'email' => ['The selected email domain is not allowed.'],
                ],
            ], 422);
        }

        $user = User::where('email', $email)->first();

        if ($user && $this->isAccountArchived($user)) {
            return response()->json([
                'message' => 'This account is unavailable for API access.',
            ], 403);
        }

        if ($user && $this->isAccountLocked($user)) {
            $secondsRemaining = max(1, now()->diffInSeconds($user->locked_until, false) * -1);

            return response()->json([
                'message' => 'This account is temporarily locked.',
                'locked' => true,
                'retry_after' => $secondsRemaining,
            ], 423);
        }

        if (! $user || ! Hash::check($password, $user->password)) {
            if ($user) {
                $failedAttempts = (int) $user->failed_login_attempts + 1;
                $updateData = ['failed_login_attempts' => $failedAttempts];

                if ($failedAttempts >= $this->apiLockoutThreshold) {
                    $updateData['locked_until'] = now()->addMinutes($this->apiLockoutMinutes);
                    $user->update($updateData);

                    return response()->json([
                        'message' => 'Authentication failed.',
                        'locked' => true,
                        'retry_after' => $this->apiLockoutMinutes * 60,
                    ], 423);
                }

                $user->update($updateData);
            }

            return response()->json([
                'message' => 'Authentication failed.',
            ], 401);
        }

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Unable to complete authentication.',
            ], 503);
        }

        return response()->json($this->buildTokenResponse($token, $user));
    }

    /**
     * API Register
     */
    public function apiRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|string|email:rfc,dns|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'max:255',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'not_in:password,12345678,123456789,qwerty,admin123,letmein,welcome',
            ],
        ], [
            'password.regex' => 'Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.',
            'password.not_in' => 'This password is too common. Please choose a stronger password.',
        ]);

        $email = strtolower(trim($validated['email']));

        if (! $this->hasAllowedEmailDomain($email)) {
            return response()->json([
                'message' => 'Invalid registration request.',
                'errors' => [
                    'email' => ['The selected email domain is not allowed.'],
                ],
            ], 422);
        }

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            if ($this->isAccountArchived($existingUser)) {
                return response()->json([
                    'message' => 'This account is unavailable for registration.',
                ], 403);
            }

            return response()->json([
                'message' => 'Invalid registration request.',
                'errors' => [
                    'email' => ['The email has already been taken.'],
                ],
            ], 422);
        }

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => $email,
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Registration completed, but automatic sign-in is unavailable.',
            ], 201);
        }

        return response()->json($this->buildTokenResponse($token, $user), 201);
    }

    /**
     * API Get Current User (JWT protected)
     */
    public function apiUser(Request $request)
    {
        try {
            $user = $request->user() ?: JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json(['message' => 'Authenticated user not found.'], 404);
            }

            if ($this->isAccountArchived($user)) {
                return response()->json(['message' => 'This account is unavailable.'], 403);
            }

            return response()->json([
                'user' => $this->transformApiUser($user),
            ]);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Invalid or expired authentication token.'], 401);
        }
    }

    /**
     * API Logout (JWT)
     */
    public function apiLogout(Request $request)
    {
        try {
            $user = $request->user();

            if ($user && $this->isAccountArchived($user)) {
                return response()->json(['message' => 'This account is unavailable.'], 403);
            }

            JWTAuth::parseToken()->invalidate(true);

            return response()->json(['message' => 'Logged out successfully.']);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Invalid or expired authentication token.'], 401);
        }
    }

    /**
     * API Refresh Token
     */
    public function apiRefreshToken(Request $request)
    {
        try {
            $user = $request->user();

            if ($user && $this->isAccountArchived($user)) {
                return response()->json(['message' => 'This account is unavailable.'], 403);
            }

            $newToken = JWTAuth::parseToken()->refresh();
            $refreshedUser = JWTAuth::setToken($newToken)->toUser();

            if (! $refreshedUser || $this->isAccountArchived($refreshedUser)) {
                return response()->json(['message' => 'This account is unavailable.'], 403);
            }

            if ($this->isAccountLocked($refreshedUser)) {
                return response()->json([
                    'message' => 'This account is temporarily locked.',
                    'locked' => true,
                ], 423);
            }

            return response()->json($this->buildTokenResponse($newToken, $refreshedUser));
        } catch (JWTException $e) {
            return response()->json(['message' => 'Invalid or expired authentication token.'], 401);
        }
    }

    /**
     * API Admin Dashboard
     */
    public function apiDashboard(Request $request)
    {
        $user = $request->user();
        if (! in_array($user->role, ['mis', 'school_admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $totalConcerns = \App\Models\Concern::count();
        $pendingConcerns = \App\Models\Concern::where('status', 'Pending')->count();
        $resolvedConcerns = \App\Models\Concern::where('status', 'Resolved')->count();
        $totalUsers = User::count();

        return response()->json([
            'concerns' => ['total' => $totalConcerns, 'pending' => $pendingConcerns, 'resolved' => $resolvedConcerns],
            'users' => ['total' => $totalUsers],
        ]);
    }

    protected function hasAllowedEmailDomain(string $email): bool
    {
        return str_ends_with(strtolower($email), '@novaliches.sti.edu.ph');
    }

    protected function isAccountArchived(User $user): bool
    {
        return (bool) ($user->is_archived || $user->archive_folder_id);
    }

    protected function isAccountLocked(User $user): bool
    {
        return ! empty($user->locked_until) && now()->lessThan($user->locked_until);
    }

    protected function transformApiUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }

    protected function buildTokenResponse(string $token, User $user): array
    {
        return [
            'user' => $this->transformApiUser($user),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
    }
}