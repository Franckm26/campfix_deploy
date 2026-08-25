<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Account Recovery - CampFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .recovery-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .recovery-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        .recovery-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .recovery-icon {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-recovery {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-recovery:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .alert {
            border-radius: 15px;
            border: none;
            margin-bottom: 20px;
        }
        .success-message {
            white-space: pre-line;
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-card">
            <div class="recovery-header">
                <div class="recovery-icon">
                    <i class="fas fa-unlock-alt"></i>
                </div>
                <h2 class="h3 mb-2">Emergency Account Recovery</h2>
                <p class="text-muted">Unlock and reset password for locked accounts</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <div class="success-message">{{ session('success') }}</div>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ url('/login') }}" class="btn btn-recovery">
                        <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                    </a>
                </div>
            @else
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('emergency.unlock-reset') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-envelope me-1"></i>Email Address
                        </label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-key me-1"></i>New Password
                        </label>
                        <input type="password" name="new_password" class="form-control" required minlength="8">
                        <div class="form-text">Minimum 8 characters</div>
                        @error('new_password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-shield-alt me-1"></i>Emergency Access Key
                        </label>
                        <input type="password" name="emergency_key" class="form-control" required>
                        <div class="form-text">Contact system administrator for emergency key</div>
                        @error('emergency_key')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-recovery">
                            <i class="fas fa-unlock me-2"></i>Unlock & Reset Password
                        </button>
                        <a href="{{ url('/login') }}" class="btn btn-link text-muted">
                            <i class="fas fa-arrow-left me-1"></i>Back to Login
                        </a>
                    </div>
                </form>

                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="mb-2"><i class="fas fa-info-circle text-info me-1"></i>Emergency Recovery Info</h6>
                    <small class="text-muted">
                        This tool can:
                        <ul class="mb-0 mt-1">
                            <li>Unlock locked accounts</li>
                            <li>Reset forgotten passwords</li>
                            <li>Restore account access</li>
                        </ul>
                        Requires valid emergency access key for security.
                    </small>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>