@extends('superadmin.layout')

@section('page_title', 'Edit User')

@section('content')
<div style="max-width:600px">
    <a href="{{ route('superadmin.users') }}" class="sa-btn sa-btn-ghost sa-btn-sm mb-4">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>

    <div class="sa-card">
        <h2 style="font-size:16px;font-weight:600;color:var(--sa-text);margin:0 0 4px">Edit User</h2>
        <p style="font-size:12px;color:var(--sa-muted);margin:0 0 20px">UUID: {{ $user->uuid }}</p>

        @if($errors->any())
            <div class="sa-alert sa-alert-error">
                <ul style="margin:0;padding-left:16px">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.users.update', $user->uuid) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="sa-label">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="sa-input" required>
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="sa-input" required>
                </div>
                <div class="col-md-6">
                    <label class="sa-label">New Password <span style="color:var(--sa-muted)">(leave blank to keep)</span></label>
                    <input type="password" name="password" class="sa-input" minlength="8">
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="sa-input">
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Role *</label>
                    <select name="role" class="sa-input" required>
                        @foreach(['student','faculty','maintenance','mis','school_admin','building_admin','academic_head','program_head','principal_assistant','superadmin'] as $r)
                            <option value="{{ $r }}" {{ old('role', $user->role) === $r ? 'selected' : '' }}>{{ str_replace('_',' ',ucfirst($r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Department</label>
                    <input type="text" name="department" value="{{ old('department', $user->department) }}" class="sa-input">
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="sa-input">
                </div>

                {{-- Status info --}}
                <div class="col-12">
                    <div style="background:rgba(255,255,255,.03);border:1px solid var(--sa-border);border-radius:8px;padding:14px">
                        <div style="font-size:12px;color:var(--sa-muted);margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:.8px">Account Status</div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            @if($user->is_deleted)
                                <span class="sa-badge sa-badge-red">Deleted</span>
                            @elseif($user->is_archived)
                                <span class="sa-badge sa-badge-yellow">Archived</span>
                            @else
                                <span class="sa-badge sa-badge-green">Active</span>
                            @endif
                            @if($user->locked_until && $user->locked_until > now())
                                <span class="sa-badge sa-badge-red"><i class="fas fa-lock me-1"></i>Locked until {{ $user->locked_until->format('M d g:i A') }}</span>
                            @endif
                            @if($user->force_password_change)
                                <span class="sa-badge sa-badge-yellow">Force PW Change</span>
                            @endif
                            @if($user->is_superadmin)
                                <span class="sa-badge sa-badge-purple"><i class="fas fa-shield-halved me-1"></i>Superadmin</span>
                            @endif
                        </div>
                        <div style="font-size:11px;color:var(--sa-muted);margin-top:8px">
                            Joined: {{ $user->created_at->format('M d, Y g:i A') }}
                            · Last updated: {{ $user->updated_at->format('M d, Y g:i A') }}
                        </div>
                    </div>
                </div>

                {{-- Module Access --}}
                <div class="col-12">
                    <div style="border:1px solid var(--sa-border);border-radius:8px;padding:16px;background:rgba(255,255,255,.02)">
                        <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:12px">
                            <i class="fas fa-shield-halved me-1" style="color:var(--sa-accent2)"></i> Module Access
                            <span style="font-size:11px;color:var(--sa-muted);font-weight:400;margin-left:6px">Select which modules this user can access</span>
                        </div>
                        <input type="hidden" name="use_custom_permissions" value="1">
                        @php
                            $saEditPerms = is_array($user->permissions) && count($user->permissions)
                                            ? $user->permissions
                                            : \App\Models\User::defaultPermissions($user->role);
                            $saEditMods  = \App\Models\User::allModules();
                            $saEditSubs  = \App\Models\User::subPermissions();
                        @endphp
                        <div class="row g-2">
                            @foreach($saEditMods as $key => $mod)
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="permissions[]" value="{{ $key }}"
                                           id="saedit_perm_{{ $key }}"
                                           {{ in_array($key, $saEditPerms) ? 'checked' : '' }}
                                           @if(isset($saEditSubs[$key])) onchange="saToggleEditSub('{{ $key }}',this.checked)" @endif>
                                    <label class="form-check-label" for="saedit_perm_{{ $key }}" style="font-size:13px;color:var(--sa-text)">
                                        {{ $mod['label'] }}
                                    </label>
                                </div>
                                @if(isset($saEditSubs[$key]))
                                <div id="saedit_sub_{{ $key }}" class="ms-4 mt-1{{ in_array($key,$saEditPerms) ? '' : ' d-none' }}">
                                    @foreach($saEditSubs[$key] as $subKey => $subLabel)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="permissions[]" value="{{ $subKey }}"
                                               id="saedit_perm_{{ $subKey }}"
                                               {{ in_array($subKey, $saEditPerms) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="saedit_perm_{{ $subKey }}" style="font-size:12px;color:var(--sa-muted)">
                                            {{ $subLabel }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <div style="display:flex;gap:8px;margin-top:12px">
                            <button type="button" class="sa-btn sa-btn-ghost sa-btn-sm" onclick="saEditSelectAll(true)">
                                <i class="fas fa-check-double"></i> Select All
                            </button>
                            <button type="button" class="sa-btn sa-btn-ghost sa-btn-sm" onclick="saEditSelectAll(false)">
                                <i class="fas fa-xmark"></i> Clear All
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-12" style="display:flex;gap:10px;margin-top:8px">
                    <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    <a href="{{ route('superadmin.users') }}" class="sa-btn sa-btn-ghost">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function saToggleEditSub(parent, checked) {
    const sub = document.getElementById('saedit_sub_' + parent);
    if (!sub) return;
    sub.classList.toggle('d-none', !checked);
    if (!checked) sub.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
}

function saEditSelectAll(checked) {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = checked);
    const sub = document.getElementById('saedit_sub_users');
    if (sub) sub.classList.toggle('d-none', !checked);
}
</script>
@endsection
