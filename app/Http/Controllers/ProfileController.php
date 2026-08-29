<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SupabaseStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|regex:/^09[0-9]{9}$/',
            'backup_email' => 'nullable|email|max:255|different:email',
        ]);

        // Track changes for email notification
        $changes = [];
        
        if ($user->name !== $request->name) {
            $changes['name'] = [
                'from' => $user->name,
                'to' => $request->name
            ];
        }
        
        if ($user->phone !== $request->phone) {
            $changes['phone'] = [
                'from' => $user->phone,
                'to' => $request->phone
            ];
        }
        
        if ($user->backup_email !== $request->backup_email) {
            $changes['backup_email'] = [
                'from' => $user->backup_email,
                'to' => $request->backup_email
            ];
        }

        // Update user data
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->backup_email = $request->backup_email;
        $user->save();

        // Send email notification if there are changes
        if (!empty($changes)) {
            try {
                // Send to primary email
                \Mail::to($user->email)->send(new \App\Mail\ProfileUpdatedNotification($user, $changes, 'self'));
                
                // Also send to backup email if it exists and is different
                if ($user->backup_email && $user->backup_email !== $user->email) {
                    \Mail::to($user->backup_email)->send(new \App\Mail\ProfileUpdatedNotification($user, $changes, 'self'));
                }
                
                \Log::info('Profile update notification sent', [
                    'user_id' => $user->id,
                    'changes' => array_keys($changes),
                    'primary_email' => $user->email,
                    'backup_email' => $user->backup_email
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send profile update notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                // Don't fail the update if email fails
            }
        }

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'not_in:password,12345678,123456789,qwerty,admin123,letmein,welcome',
            ],
        ], [
            'new_password.regex' => 'Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.',
            'new_password.not_in' => 'This password is too common. Please choose a stronger password.',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Password updated successfully!');
    }


    public function uploadProfilePicture(Request $request)
    {
        try {
            $request->validate([
                'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            ]);

            $user = Auth::user();

            if ($request->hasFile('profile_picture')) {
                $supabaseStorage = new SupabaseStorage();

                // Delete old picture if exists and it's a Supabase URL
                if ($user->profile_picture && str_contains($user->profile_picture, 'supabase')) {
                    $supabaseStorage->delete($user->profile_picture);
                    Log::info('Deleted old profile picture from Supabase', ['url' => $user->profile_picture]);
                }

                // Upload new picture to Supabase Storage
                $publicUrl = $supabaseStorage->upload($request->file('profile_picture'), 'profile_pictures');

                if (!$publicUrl) {
                    Log::error('Failed to upload profile picture to Supabase');
                    
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => 'Failed to upload profile picture to storage.'], 500);
                    }
                    
                    return redirect()->route('profile.index')->with('error', 'Failed to upload profile picture.');
                }

                // Update user with the public URL
                $user->profile_picture = $publicUrl;
                $user->save();

                Log::info('Profile picture uploaded successfully', [
                    'user_id' => $user->id,
                    'url' => $publicUrl
                ]);

                // Check if it's an AJAX request
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true, 
                        'message' => 'Profile picture updated successfully!',
                        'url' => $publicUrl
                    ]);
                }

                return redirect()->route('profile.index')->with('success', 'Profile picture updated successfully!');
            }

            // Check if it's an AJAX request
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
            }

            return redirect()->route('profile.index')->with('error', 'No file uploaded.');
            
        } catch (\Exception $e) {
            Log::error('Profile picture upload error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
            }
            
            return redirect()->route('profile.index')->with('error', 'Failed to upload profile picture: ' . $e->getMessage());
        }
    }

    public function removeProfilePicture(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user->profile_picture) {
                // Check if it's a Supabase URL and delete from Supabase
                if (str_contains($user->profile_picture, 'supabase')) {
                    $supabaseStorage = new SupabaseStorage();
                    $supabaseStorage->delete($user->profile_picture);
                    Log::info('Deleted profile picture from Supabase', ['url' => $user->profile_picture]);
                } else {
                    // Legacy: Delete from local storage if it exists
                    if (Storage::disk('public')->exists($user->profile_picture)) {
                        Storage::disk('public')->delete($user->profile_picture);
                    }

                    // Also delete from public/storage if it exists (for Windows compatibility)
                    $publicPath = public_path('storage/'.$user->profile_picture);
                    if (file_exists($publicPath)) {
                        unlink($publicPath);
                    }
                }

                // Clear the profile_picture field
                $user->profile_picture = null;
                $user->save();

                Log::info('Profile picture removed', ['user_id' => $user->id]);
            }

            // Check if it's an AJAX request
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Profile picture removed successfully!']);
            }

            return redirect()->route('profile.index')->with('success', 'Profile picture removed successfully!');
            
        } catch (\Exception $e) {
            Log::error('Profile picture removal error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to remove profile picture.'], 500);
            }
            
            return redirect()->route('profile.index')->with('error', 'Failed to remove profile picture.');
        }
    }
}
