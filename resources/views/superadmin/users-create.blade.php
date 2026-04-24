@extends('superadmin.layout')

@section('page_title', 'Create User')

@section('content')
<div style="max-width:600px">
    <a href="{{ route('superadmin.users') }}" class="sa-btn sa-btn-ghost sa-btn-sm mb-4">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>

    <div class="sa-card">
        <h2 style="font-size:16px;font-weight:600;color:var(--sa-text);margin:0 0 20px">Create New User</h2>

        @if($errors->any())
            <div class="sa-alert sa-alert-error">
                <ul style="margin:0;padding-left:16px">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.users.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="sa-label">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="sa-input" required>
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="sa-input" required>
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Password *</label>
                    <input type="password" name="password" class="sa-input" required minlength="8">
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Confirm Password *</label>
                    <input type="password" name="password_confirmation" class="sa-input" required>
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Role *</label>
                    <select name="role" id="saCreateRole" class="sa-input" required onchange="saOnRoleChange(this.value)">
                        @foreach(['student','faculty','maintenance','mis','school_admin','building_admin','academic_head','program_head','principal_assistant','superadmin'] as $r)
                            <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ str_replace('_',' ',ucfirst($r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Department</label>
                    <input type="text" name="department" value="{{ old('department') }}" class="sa-input">
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="sa-input">
                </div>
                <div class="col-md-6" style="display:flex;align-items:center;gap:10px;padding-top:24px">
                    <input type="checkbox" name="force_password_change" id="fpc" value="1" checked style="width:16px;height:16px;accent-color:var(--sa-accent)">
                    <label for="fpc" style="font-size:13px;color:var(--sa-muted);cursor:pointer">Force password change on first login</label>
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
                            $saMods    = \App\Models\User::allModules();
                            $saSubPerms= \App\Models\User::subPermissions();
                            $saOldPerms= old('permissions', \App\Models\User::defaultPermissions(old('role','student')));
                        @endphp
                        <div class="row g-2">
                            @foreach($saMods as $key => $mod)
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="permissions[]" value="{{ $key }}"
                                           id="sa_perm_{{ $key }}"
                                           {{ in_array($key, $saOldPerms) ? 'checked' : '' }}
                                           @if(isset($saSubPerms[$key])) onchange="saToggleSub('create','{{ $key }}',this.checked)" @endif>
                                    <label class="form-check-label" for="sa_perm_{{ $key }}" style="font-size:13px;color:var(--sa-text)">
                                        {{ $mod['label'] }}
                                    </label>
                                </div>
                                @if(isset($saSubPerms[$key]))
                                <div id="sa_create_sub_{{ $key }}" class="ms-4 mt-1{{ in_array($key,$saOldPerms) ? '' : ' d-none' }}">
                                    @foreach($saSubPerms[$key] as $subKey => $subLabel)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="permissions[]" value="{{ $subKey }}"
                                               id="sa_perm_{{ $subKey }}"
                                               {{ in_array($subKey, $saOldPerms) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sa_perm_{{ $subKey }}" style="font-size:12px;color:var(--sa-muted)">
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
                            <button type="button" class="sa-btn sa-btn-ghost sa-btn-sm" onclick="saSelectAll('create',true)">
                                <i class="fas fa-check-double"></i> Select All
                            </button>
                            <button type="button" class="sa-btn sa-btn-ghost sa-btn-sm" onclick="saSelectAll('create',false)">
                                <i class="fas fa-xmark"></i> Clear All
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-12" style="display:flex;gap:10px;margin-top:8px">
                    <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-plus"></i> Create User</button>
                    <a href="{{ route('superadmin.users') }}" class="sa-btn sa-btn-ghost">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
const saRoleDefaults = {
    mis:                  ['concerns','reports','events','users','users_create','users_archive','users_lock','users_unlock','users_edit','users_delete','categories','logs','analytics','mis_tasks','settings'],
    school_admin:         ['concerns','reports','events','analytics','settings'],
    building_admin:       ['concerns','reports','events','analytics','settings'],
    academic_head:        ['events','settings'],
    program_head:         ['events','settings'],
    principal_assistant:  ['events','settings'],
    maintenance:          ['reports','concerns','settings'],
    faculty:              ['events','concerns','settings'],
    student:              ['concerns','settings'],
    superadmin:           ['concerns','reports','events','users','users_create','users_archive','users_lock','users_unlock','users_edit','users_delete','categories','logs','analytics','mis_tasks','settings'],
};

function saOnRoleChange(role) {
    const defaults = saRoleDefaults[role] || ['settings'];
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
        cb.checked = defaults.includes(cb.value);
    });
    saToggleSub('create', 'users', defaults.includes('users'));
}

function saToggleSub(prefix, parent, checked) {
    const sub = document.getElementById('sa_' + prefix + '_sub_' + parent);
    if (!sub) return;
    sub.classList.toggle('d-none', !checked);
    if (!checked) sub.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
}

function saSelectAll(prefix, checked) {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = checked);
    const sub = document.getElementById('sa_' + prefix + '_sub_users');
    if (sub) sub.classList.toggle('d-none', !checked);
}

// Init on load
document.addEventListener('DOMContentLoaded', function () {
    const role = document.getElementById('saCreateRole')?.value || 'student';
    saOnRoleChange(role);
});
</script>
@endsection
