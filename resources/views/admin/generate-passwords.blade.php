@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-key me-2"></i>Generate User Passwords</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('errors'))
                        <div class="alert alert-danger">
                            <strong>Errors encountered:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach(session('errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>About this tool:</strong> This will generate new secure passwords for users and send them via email with their login credentials.
                    </div>

                    <form method="POST" action="{{ route('admin.passwords.generate') }}" id="generatePasswordForm">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Scope <span class="text-danger">*</span></label>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="scope" id="scopeAll" value="all" required onchange="updateScopeFields()">
                                <label class="form-check-label" for="scopeAll">
                                    <strong>All Users</strong> - Generate passwords for all users in the system
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="scope" id="scopeRole" value="role" onchange="updateScopeFields()">
                                <label class="form-check-label" for="scopeRole">
                                    <strong>By Role</strong> - Generate passwords for users with a specific role
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="scope" id="scopeEmail" value="email" onchange="updateScopeFields()">
                                <label class="form-check-label" for="scopeEmail">
                                    <strong>Single User</strong> - Generate password for a specific user by email
                                </label>
                            </div>
                        </div>

                        <div id="roleField" class="mb-3" style="display: none;">
                            <label class="form-label fw-semibold">Select Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select">
                                <option value="">-- Select Role --</option>
                                <option value="student">Student</option>
                                <option value="faculty">Faculty</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="mis">MIS</option>
                                <option value="school_admin">School Admin</option>
                                <option value="building_admin">Building Admin</option>
                                <option value="academic_head">Academic Head</option>
                                <option value="program_head">Program Head</option>
                                <option value="principal_assistant">Principal Assistant</option>
                            </select>
                        </div>

                        <div id="emailField" class="mb-3" style="display: none;">
                            <label class="form-label fw-semibold">User Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="user@example.com">
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="exclude_superadmin" id="excludeSuperadmin" value="1" checked>
                            <label class="form-check-label" for="excludeSuperadmin">
                                <strong>Exclude Superadmin Users</strong> (Recommended for security)
                            </label>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This action will:
                            <ul class="mb-0 mt-2">
                                <li>Generate new passwords for the selected users</li>
                                <li>Send email notifications with the new credentials</li>
                                <li>Users' old passwords will no longer work</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary btn-lg" onclick="confirmGeneration()">
                                <i class="fas fa-key me-2"></i>Generate Passwords
                            </button>
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Users
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Notes</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Generated passwords are 12 characters long with mixed case, numbers, and special characters</li>
                            <li>Passwords are sent via email - ensure your mail configuration is working</li>
                            <li>All operations are logged for security auditing</li>
                            <li>Users will receive a welcome email with their new credentials</li>
                            <li>It's recommended to exclude superadmin accounts for security</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateScopeFields() {
    const scope = document.querySelector('input[name="scope"]:checked').value;
    const roleField = document.getElementById('roleField');
    const emailField = document.getElementById('emailField');

    roleField.style.display = scope === 'role' ? 'block' : 'none';
    emailField.style.display = scope === 'email' ? 'block' : 'none';

    // Clear fields when hidden
    if (scope !== 'role') {
        document.querySelector('[name="role"]').value = '';
    }
    if (scope !== 'email') {
        document.querySelector('[name="email"]').value = '';
    }
}

function confirmGeneration() {
    const form = document.getElementById('generatePasswordForm');
    const scope = document.querySelector('input[name="scope"]:checked').value;
    let message = 'Are you sure you want to generate new passwords? This will:\n\n';
    message += '• Generate new secure passwords\n';
    message += '• Send email notifications to users\n';
    message += '• Invalidate old passwords\n\n';

    if (scope === 'all') {
        message += 'This will affect ALL users in the system!';
    } else if (scope === 'role') {
        const role = document.querySelector('[name="role"]').value;
        if (!role) {
            alert('Please select a role first.');
            return;
        }
        message += 'This will affect all users with the selected role.';
    } else if (scope === 'email') {
        const email = document.querySelector('[name="email"]').value;
        if (!email) {
            alert('Please enter an email address first.');
            return;
        }
        message += 'This will affect the user: ' + email;
    }

    if (confirm(message)) {
        form.submit();
    }
}
</script>
@endsection
