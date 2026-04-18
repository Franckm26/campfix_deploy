<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
        ]);

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->save();

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
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_picture')) {
            // Delete old picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new picture
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');

            // For Windows/XAMPP compatibility, also copy to public/storage
            $sourcePath = storage_path('app/public/'.$path);
            $destPath = public_path('storage/'.$path);
            $destDir = dirname($destPath);

            if (! is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            if (file_exists($sourcePath)) {
                copy($sourcePath, $destPath);
            }

            // Update user
            $user->profile_picture = $path;
            $user->save();

            // Check if it's an AJAX request
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Profile picture updated successfully!']);
            }

            return redirect()->route('profile.index')->with('success', 'Profile picture updated successfully!');
        }

        // Check if it's an AJAX request
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Failed to upload profile picture.'], 400);
        }

        return redirect()->route('profile.index')->with('error', 'Failed to upload profile picture.');
    }

    public function removeProfilePicture(Request $request)
    {
        $user = Auth::user();

        if ($user->profile_picture) {
            // Delete the file from storage
            if (Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Also delete from public/storage if it exists (for Windows compatibility)
            $publicPath = public_path('storage/'.$user->profile_picture);
            if (file_exists($publicPath)) {
                unlink($publicPath);
            }

            // Clear the profile_picture field
            $user->profile_picture = null;
            $user->save();
        }

        // Check if it's an AJAX request
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Profile picture removed successfully!']);
        }

        return redirect()->route('profile.index')->with('success', 'Profile picture removed successfully!');
    }
}
