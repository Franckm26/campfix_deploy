<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>First Login - Set Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth-first-login.css') }}">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center pt-4">
                        <h3 class="mb-1"><i class="fas fa-user-lock"></i> Welcome!</h3>
                        <p class="text-muted">Please set your new password and contact number</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        @if(session('info'))
                            <div class="alert alert-info">
                                {{ session('info') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('auth.first-login-password.update') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <!-- Strength bar -->
                                <div class="mt-2" id="strengthBarWrap" style="display:none">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px">
                                            <div id="strengthBar" class="progress-bar" style="width:0%;transition:width .3s,background .3s"></div>
                                        </div>
                                        <small id="strengthLabel" class="fw-semibold" style="min-width:52px;font-size:12px"></small>
                                    </div>
                                </div>
                                <!-- Requirements tooltip -->
                                <div id="passwordRequirements" class="mt-1 p-2 rounded" style="display:none;background:#f8f9fa;font-size:12px;border:1px solid #dee2e6">
                                    <div class="fw-semibold mb-1" style="font-size:12px">Password must include:</div>
                                    <div id="req-length"  class="req-item"><i class="fas fa-times-circle text-danger me-1"></i>8-20 <strong>Characters</strong></div>
                                    <div id="req-upper"   class="req-item"><i class="fas fa-times-circle text-danger me-1"></i>At least one <strong>capital letter</strong></div>
                                    <div id="req-number"  class="req-item"><i class="fas fa-times-circle text-danger me-1"></i>At least one <strong>number</strong></div>
                                    <div id="req-nospace" class="req-item"><i class="fas fa-times-circle text-danger me-1"></i><strong>No spaces</strong></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" required autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary" id="toggleConfirm">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small id="matchMsg" class="mt-1 d-block" style="font-size:12px"></small>
                            </div>

                            <div class="mb-4">
                                <label for="phone" class="form-label">Contact Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="09XXXXXXXXX" maxlength="11" pattern="09[0-9]{9}" required>
                                </div>
                                <small class="text-muted" style="font-size:11px">11-digit PH mobile number (e.g., 09123456789)</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Save & Continue
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const pwInput   = document.getElementById('password');
    const pwConfirm = document.getElementById('password_confirmation');
    const bar       = document.getElementById('strengthBar');
    const label     = document.getElementById('strengthLabel');
    const barWrap   = document.getElementById('strengthBarWrap');
    const reqBox    = document.getElementById('passwordRequirements');
    const matchMsg  = document.getElementById('matchMsg');

    function setReq(id, pass) {
        const el = document.getElementById(id);
        const icon = el.querySelector('i');
        if (pass) {
            icon.className = 'fas fa-check-circle text-success me-2';
            el.style.color = '#198754';
        } else {
            icon.className = 'fas fa-times-circle text-danger me-2';
            el.style.color = '#dc3545';
        }
    }

    pwInput.addEventListener('focus', () => {
        barWrap.style.display = 'block';
        reqBox.style.display  = 'block';
    });

    pwInput.addEventListener('input', () => {
        const val = pwInput.value;
        const okLength  = val.length >= 8 && val.length <= 20;
        const okUpper   = /[A-Z]/.test(val);
        const okNumber  = /[0-9]/.test(val);
        const okNoSpace = val.length > 0 && !/\s/.test(val);

        setReq('req-length',  okLength);
        setReq('req-upper',   okUpper);
        setReq('req-number',  okNumber);
        setReq('req-nospace', okNoSpace);

        const score = [okLength, okUpper, okNumber, okNoSpace].filter(Boolean).length;
        const levels = [
            { pct: 25,  color: '#dc3545', text: 'Weak'   },
            { pct: 50,  color: '#fd7e14', text: 'Fair'   },
            { pct: 75,  color: '#ffc107', text: 'Medium' },
            { pct: 100, color: '#198754', text: 'Strong' },
        ];
        const lvl = levels[score - 1] || { pct: 0, color: '#dee2e6', text: '' };
        bar.style.width      = lvl.pct + '%';
        bar.style.background = lvl.color;
        label.textContent    = lvl.text;
        label.style.color    = lvl.color;

        checkMatch();
    });

    pwInput.addEventListener('blur', () => {
        if (!pwInput.value) {
            barWrap.style.display = 'none';
            reqBox.style.display  = 'none';
        }
    });

    pwConfirm.addEventListener('input', checkMatch);

    function checkMatch() {
        if (!pwConfirm.value) { matchMsg.textContent = ''; return; }
        if (pwInput.value === pwConfirm.value) {
            matchMsg.textContent = '✓ Passwords match';
            matchMsg.style.color = '#198754';
        } else {
            matchMsg.textContent = '✗ Passwords do not match';
            matchMsg.style.color = '#dc3545';
        }
    }

    // Toggle show/hide password
    document.getElementById('togglePassword').addEventListener('click', function () {
        const type = pwInput.type === 'password' ? 'text' : 'password';
        pwInput.type = type;
        this.querySelector('i').className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    });

    document.getElementById('toggleConfirm').addEventListener('click', function () {
        const type = pwConfirm.type === 'password' ? 'text' : 'password';
        pwConfirm.type = type;
        this.querySelector('i').className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    });

    // Block submit if requirements not met
    document.querySelector('form').addEventListener('submit', function (e) {
        const val = pwInput.value;
        const ok = val.length >= 8 && val.length <= 20 && /[A-Z]/.test(val) && /[0-9]/.test(val) && !/\s/.test(val);
        if (!ok) {
            e.preventDefault();
            barWrap.style.display = 'block';
            reqBox.style.display  = 'block';
            pwInput.focus();
        }
        if (pwInput.value !== pwConfirm.value) {
            e.preventDefault();
            pwConfirm.focus();
        }
    });
    </script>
</body>
</html>
