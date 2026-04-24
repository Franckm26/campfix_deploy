@extends('layouts.app')

@section('styles')
<style>
    .settings-container {
        max-width: 100%;
        margin: 0 auto;
        padding: 0 20px;
    }

    @media (min-width: 768px) {
        .settings-container {
            max-width: 800px;
            padding: 0;
        }
    }
    
    .settings-card {
        background: var(--card-bg);
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 16px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    
    .settings-header {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items:center;
        gap: 10px;
        background: #f8f9fa;
    }
    
    .settings-header i {
        font-size: 16px;
        color: #5e5ce6;
    }
    
    .settings-header h6 {
        margin: 0;
        color: #1e293b;
        font-weight: 600;
        font-size: 15px;
    }
    
    .settings-body {
        padding: 20px;
    }
    
    .settings-section {
        margin-bottom: 20px;
    }
    
    .settings-section:last-child {
        margin-bottom: 0;
    }
    
    .settings-section h5 {
        color: #64748b;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        font-weight: 600;
    }
    
    .settings-body {
        padding: 16px;
    }
    
    .settings-section:last-child {
        margin-bottom: 0;
    }
    
    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .setting-item:last-child {
        border-bottom: none;
    }
    
    .setting-info {
        flex: 1;
    }
    
    .setting-label {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 2px;
        font-size: 14px;
    }
    
    .setting-description {
        font-size: 12px;
        color: #64748b;
        line-height: 1.4;
    }
    
    /* Toggle Switch */
    .toggle-switch {
        position: relative;
        width: 50px;
        height: 26px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #9e9e9e;
        transition: 0.3s;
        border-radius: 26px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }
    
    input:checked + .toggle-slider {
        background-color: #5e5ce6;
    }
    
    input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }
    
    /* Form Controls */
    .form-select-sm, .form-control-sm {
        padding: 6px 12px;
        font-size: 13px;
    }
    
    .form-select, .form-control {
        font-size: 14px;
    }
    
    .btn-save {
        background: #5e5ce6;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s;
    }
    
    .btn-save:hover {
        background: #4f4dd6;
        color: white;
        opacity: 1;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(94, 92, 230, 0.3);
    }
    
    .btn-add {
        background: #5e5ce6;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s;
    }
    
    .btn-add:hover {
        background: #4f4dd6;
        color: white;
        transform: translateY(-1px);
    }
    
    .btn-primary {
        background: #5e5ce6;
        border-color: #5e5ce6;
        color: white;
    }
    
    .btn-primary:hover {
        background: #4f4dd6;
        border-color: #4f4dd6;
        color: white;
    }
    
    .btn-danger {
        background: #c62828;
        border-color: #c62828;
        color: white;
    }
    
    .btn-danger:hover {
        background: #e53935;
        border-color: #e53935;
        color: white;
    }
    
    .btn-secondary {
        background: #757575;
        border-color: #757575;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #9e9e9e;
        border-color: #9e9e9e;
        color: white;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 16px;
        border-left: 4px solid #10b981;
        font-size: 14px;
    }
    
    .settings-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .settings-tab {
        padding: 10px 20px;
        background: #f8f9fa;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        color: #64748b;
        text-decoration: none;
        transition: all 0.2s;
        font-weight: 600;
        font-size: 13px;
    }
    
    .settings-tab:hover {
        background: #f1f5f9;
        border-color: #5e5ce6;
        color: #5e5ce6;
    }
    
    .settings-tab.active {
        background: #5e5ce6;
        color: white;
        border-color: #5e5ce6;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
</style>
@endsection

@section('page_title')
<div style="display:flex;align-items:center;gap:12px">
    <img src="{{ asset('Campfix/Images/images.png') }}" alt="STI Logo" style="height:40px">
    <h2 style="margin:0">Home</h2>
</div>
@endsection

@section('content')
@php
    $isTagalog = app()->getLocale() === 'tl';
@endphp
<div class="settings-container">
    
    <!-- Success Message -->
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <!-- Settings Tabs -->
    <div class="settings-tabs">
        <a href="#" class="settings-tab active" onclick="showTab(event, 'notifications')">
            <i class="fas fa-bell"></i> {{ $isTagalog ? 'Mga Abiso' : 'Notifications' }}
        </a>
        <a href="#" class="settings-tab" onclick="showTab(event, 'preferences')">
            <i class="fas fa-sliders-h"></i> {{ $isTagalog ? 'Mga Kagustuhan' : 'Preferences' }}
        </a>
        <a href="#" class="settings-tab" onclick="showTab(event, 'privacy')">
            <i class="fas fa-shield-alt"></i> {{ $isTagalog ? 'Pagkapribado' : 'Privacy' }}
        </a>
        <a href="#" class="settings-tab" onclick="showTab(event, 'security')">
            <i class="fas fa-lock"></i> {{ $isTagalog ? 'Seguridad' : 'Security' }}
        </a>
    </div>
    
    <!-- Notifications Tab -->
    <div id="notifications" class="tab-content active">
        <div class="settings-card">
            <div class="settings-header">
                <i class="fas fa-bell"></i>
                <h6>{{ $isTagalog ? 'Mga Setting ng Abiso' : 'Notification Settings' }}</h6>
            </div>
            <div class="settings-body">
                <form action="{{ route('settings.notifications') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="settings-section">
                        <h5>{{ $isTagalog ? 'Mga Channel ng Abiso' : 'Notification Channels' }}</h5>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">{{ $isTagalog ? 'Mga Abiso sa Email' : 'Email Notifications' }}</div>
                                <div class="setting-description">{{ $isTagalog ? 'Tumanggap ng mga update sa pamamagitan ng email' : 'Receive updates via email' }}</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="email_notifications" value="1" {{ $user->email_notifications ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">{{ $isTagalog ? 'Mga Abiso sa SMS' : 'SMS Notifications' }}</div>
                                <div class="setting-description">{{ $isTagalog ? 'Tumanggap ng mga update sa pamamagitan ng SMS' : 'Receive updates via SMS' }}</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="sms_notifications" value="1" {{ $user->sms_notifications ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">{{ $isTagalog ? 'Push Notifications' : 'Push Notifications' }}</div>
                                <div class="setting-description">{{ $isTagalog ? 'Tumanggap ng mga update sa app' : 'Receive updates in the app' }}</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="push_notifications" value="1" {{ $user->push_notifications ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-save">{{ $isTagalog ? 'I-save ang Mga Setting ng Abiso' : 'Save Notification Settings' }}</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Preferences Tab -->
    <div id="preferences" class="tab-content">
        <div class="settings-card">
            <div class="settings-header">
                <i class="fas fa-sliders-h"></i>
                <h6>{{ $isTagalog ? 'Mga Kagustuhan sa Display' : 'Display Preferences' }}</h6>
            </div>
            <div class="settings-body">
                <form action="{{ route('settings.preferences') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="settings-section">
                        <h5>{{ $isTagalog ? 'Mga Rehiyonal na Setting' : 'Regional Settings' }}</h5>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">{{ $isTagalog ? 'Wika' : 'Language' }}</div>
                                <div class="setting-description">{{ $isTagalog ? 'Piliin ang gusto mong wika' : 'Select your preferred language' }}</div>
                            </div>
                            <select name="language" class="form-select form-select-sm" style="width: auto;">
                                <option value="en" {{ $user->language == 'en' ? 'selected' : '' }}>English</option>
                                <option value="tl" {{ $user->language == 'tl' ? 'selected' : '' }}>Filipino / Tagalog</option>
                            </select>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">{{ $isTagalog ? 'Timezone' : 'Timezone' }}</div>
                                <div class="setting-description">{{ $isTagalog ? 'Itakda ang iyong lokal na timezone' : 'Set your local timezone' }}</div>
                            </div>
                            <select name="timezone" class="form-select form-select-sm" style="width: auto;">
                                <option value="Asia/Shanghai" {{ $user->timezone == 'Asia/Shanghai' ? 'selected' : '' }}>Asia/Shanghai (UTC+8)</option>
                                <option value="Asia/Manila" {{ $user->timezone == 'Asia/Manila' ? 'selected' : '' }}>Asia/Manila (UTC+8)</option>
                                <option value="UTC" {{ $user->timezone == 'UTC' ? 'selected' : '' }}>UTC</option>
                            </select>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">{{ $isTagalog ? 'Format ng Petsa' : 'Date Format' }}</div>
                                <div class="setting-description">{{ $isTagalog ? 'Piliin kung paano ipapakita ang mga petsa' : 'Choose how dates are displayed' }}</div>
                            </div>
                            <select name="date_format" class="form-select form-select-sm" style="width: auto;">
                                <option value="Y-m-d" {{ $user->date_format == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                <option value="d/m/Y" {{ $user->date_format == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                <option value="m/d/Y" {{ $user->date_format == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                            </select>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">{{ $isTagalog ? 'Bilang ng Items kada Pahina' : 'Items Per Page' }}</div>
                                <div class="setting-description">{{ $isTagalog ? 'Bilang ng mga item na ipapakita sa mga listahan' : 'Number of items to display in lists' }}</div>
                            </div>
                            <select name="items_per_page" class="form-select form-select-sm" style="width: auto;">
                                <option value="5" {{ $user->items_per_page == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ $user->items_per_page == 10 ? 'selected' : '' }}>10</option>
                                <option value="15" {{ $user->items_per_page == 15 ? 'selected' : '' }}>15</option>
                                <option value="20" {{ $user->items_per_page == 20 ? 'selected' : '' }}>20</option>
                                <option value="25" {{ $user->items_per_page == 25 ? 'selected' : '' }}>25</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-save">{{ $isTagalog ? 'I-save ang Mga Kagustuhan' : 'Save Preferences' }}</button>
                </form>
            </div>
        </div>
        
        
        <!-- Categories Management (Admin Only) -->
        <!-- REMOVED: Category management has been moved to admin panel -->
    </div>
    
    <!-- Privacy Tab -->
    <div id="privacy" class="tab-content">
        <div class="settings-card">
            <div class="settings-header">
                <i class="fas fa-shield-alt"></i>
                <h6>Privacy Settings</h6>
            </div>
            <div class="settings-body">
                <form action="{{ route('settings.privacy') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="settings-section">
                        <h5>Profile Visibility</h5>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">Show Online Status</div>
                                <div class="setting-description">Allow others to see when you're online</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="show_online_status" value="1" {{ $user->show_online_status ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">Show Activity</div>
                                <div class="setting-description">Allow others to see your recent activity</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="show_activity" value="1" {{ $user->show_activity ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">Allow Messages</div>
                                <div class="setting-description">Allow other users to send you messages</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="allow_messages" value="1" {{ $user->allow_messages ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-save">Save Privacy Settings</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Security Tab -->
    <div id="security" class="tab-content">

        <!-- Security Misconfiguration Controls -->
        <div class="settings-card">
            <div class="settings-header">
                <i class="fas fa-shield-alt"></i>
                <h6>Security Misconfiguration Controls</h6>
            </div>
            <div class="settings-body">
                <form action="{{ route('settings.security-misconfiguration') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="settings-section">
                        <h5>Session Management</h5>

                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">Session Timeout</div>
                                <div class="setting-description">Automatically log out after period of inactivity (minutes)</div>
                            </div>
                            <select name="session_timeout_minutes" class="form-select form-select-sm" style="width: auto;">
                                <option value="15" {{ $user->session_timeout_minutes == 15 ? 'selected' : '' }}>15 minutes</option>
                                <option value="30" {{ $user->session_timeout_minutes == 30 ? 'selected' : '' }}>30 minutes</option>
                                <option value="60" {{ $user->session_timeout_minutes == 60 ? 'selected' : '' }}>1 hour</option>
                                <option value="120" {{ $user->session_timeout_minutes == 120 ? 'selected' : '' }}>2 hours</option>
                                <option value="240" {{ $user->session_timeout_minutes == 240 ? 'selected' : '' }}>4 hours</option>
                                <option value="480" {{ $user->session_timeout_minutes == 480 ? 'selected' : '' }}>8 hours</option>
                            </select>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h5>Security Notifications</h5>

                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">Security Alerts</div>
                                <div class="setting-description">Receive notifications about security events and potential misconfigurations</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="security_notifications_enabled" value="1" {{ $user->security_notifications_enabled ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h5>Password Policy</h5>

                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">Password Change Frequency</div>
                                <div class="setting-description">How often you should be reminded to change your password (days)</div>
                            </div>
                            <select name="password_change_frequency_days" class="form-select form-select-sm" style="width: auto;">
                                <option value="30" {{ $user->password_change_frequency_days == 30 ? 'selected' : '' }}>30 days</option>
                                <option value="60" {{ $user->password_change_frequency_days == 60 ? 'selected' : '' }}>60 days</option>
                                <option value="90" {{ $user->password_change_frequency_days == 90 ? 'selected' : '' }}>90 days</option>
                                <option value="180" {{ $user->password_change_frequency_days == 180 ? 'selected' : '' }}>180 days</option>
                                <option value="365" {{ $user->password_change_frequency_days == 365 ? 'selected' : '' }}>365 days</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px">Current Password (required to save settings)</label>
                        <input type="password" name="current_password" class="form-control" required style="font-size:14px">
                        @if($errors->has('current_password'))
                            <span class="text-danger" style="font-size:12px">{{ $errors->first('current_password') }}</span>
                        @endif
                    </div>

                    <button type="submit" class="btn-save">Save Security Misconfiguration Settings</button>
                </form>
            </div>
        </div>

        <!-- Account Info -->
        <div class="settings-card">
            <div class="settings-header">
                <i class="fas fa-user-circle"></i>
                <h6>Account Information</h6>
            </div>
            <div class="settings-body">
                <div class="settings-section">
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Account Type</div>
                            <div class="setting-description">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</div>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Email</div>
                            <div class="setting-description">{{ $user->email }}</div>
                        </div>
                    </div>
                    
                    @if($user->phone)
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Phone</div>
                            <div class="setting-description">{{ $user->phone }}</div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Member Since</div>
                            <div class="setting-description">{{ $user->created_at->format('F d, Y') }}</div>
                        </div>
                    </div>
                </div>
                
                <a href="/profile" class="btn-save" style="display: inline-block; text-decoration: none; text-align: center;">
                    <i class="fas fa-edit"></i> Manage Account
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function showTab(event, tabId) {
        event.preventDefault();

        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        document.querySelectorAll('.settings-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        document.getElementById(tabId).classList.add('active');
        event.currentTarget.classList.add('active');

        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '#'+tabId);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const validTabs = ['notifications', 'preferences', 'privacy', 'security'];
        const hashTab = window.location.hash ? window.location.hash.substring(1) : '';
        const activeTab = validTabs.includes(hashTab) ? hashTab : 'notifications';

        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        document.querySelectorAll('.settings-tab').forEach(tab => {
            tab.classList.remove('active');

            const onclickValue = tab.getAttribute('onclick') || '';
            if (onclickValue.includes("'" + activeTab + "'")) {
                tab.classList.add('active');
            }
        });

        const activeContent = document.getElementById(activeTab);
        if (activeContent) {
            activeContent.classList.add('active');
        }
    });
</script>
@endsection
