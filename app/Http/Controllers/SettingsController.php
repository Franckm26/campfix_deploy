<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $user = Auth::user();

        return view('settings.index', compact('user'));
    }

    /**
     * Update notification settings.
     */
    public function updateNotification(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
        ]);

        $user->email_notifications = $request->has('email_notifications');
        $user->sms_notifications = $request->has('sms_notifications');
        $user->push_notifications = $request->has('push_notifications');
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Notification settings updated successfully!');
    }

    /**
     * Update display preferences.
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'language' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'date_format' => 'nullable|string|max:20',
            'items_per_page' => 'nullable|integer|min:5|max:100',
        ]);

        $user->language = $request->language ?? 'en';
        $user->timezone = $request->timezone ?? 'Asia/Shanghai';
        $user->date_format = $request->date_format ?? 'Y-m-d';
        $user->items_per_page = $request->items_per_page ?? 10;
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Display preferences updated successfully!');
    }

    /**
     * Update theme preference (AJAX).
     */
    public function updateTheme(Request $request)
    {
        $request->validate(['theme' => 'required|in:light,dark']);
        Auth::user()->update(['theme' => $request->theme]);
        return response()->json(['success' => true]);
    }

    /**
     * Update privacy settings.
     */
    public function updatePrivacy(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'show_online_status' => 'boolean',
            'show_activity' => 'boolean',
            'allow_messages' => 'boolean',
        ]);

        $user->show_online_status = $request->has('show_online_status');
        $user->show_activity = $request->has('show_activity');
        $user->allow_messages = $request->has('allow_messages');
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Privacy settings updated successfully!');
    }

    /**
     * Update security settings.
     */
    public function updateSecurity(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'two_factor_enabled' => 'boolean',
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->two_factor_enabled = $request->has('two_factor_enabled');
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Security settings updated successfully!');
    }

    /**
     * Update security misconfiguration settings.
     */
    public function updateSecurityMisconfiguration(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'session_timeout_minutes' => 'required|integer|min:15|max:480',
            'security_notifications_enabled' => 'boolean',
            'password_change_frequency_days' => 'required|integer|min:30|max:365',
            'file_security_enabled' => 'boolean',
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->session_timeout_minutes = $request->session_timeout_minutes;
        $user->security_notifications_enabled = $request->has('security_notifications_enabled');
        $user->password_change_frequency_days = $request->password_change_frequency_days;
        $user->file_security_enabled = $request->has('file_security_enabled');
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Security misconfiguration settings updated successfully!');
    }
}
