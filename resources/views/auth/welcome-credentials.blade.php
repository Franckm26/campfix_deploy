<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to CampFix - Get Your Credentials</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .welcome-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            margin: 20px;
        }
        .logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            display: block;
        }
        .welcome-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
        }
        .welcome-subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
        }
        .search-box {
            position: relative;
            margin-bottom: 30px;
        }
        .search-box input {
            padding-left: 45px;
            height: 50px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            font-size: 16px;
        }
        .search-box input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
        }
        .credential-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            display: none;
        }
        .credential-box.show {
            display: block;
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .credential-item {
            margin-bottom: 15px;
        }
        .credential-label {
            font-weight: 600;
            color: #555;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .credential-value {
            background: white;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .copy-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }
        .copy-btn:hover {
            background: #5568d3;
        }
        .copy-btn.copied {
            background: #28a745;
        }
        .alert-info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            color: #004085;
            border-radius: 10px;
        }
        .not-found {
            text-align: center;
            color: #dc3545;
            padding: 20px;
            display: none;
        }
        .not-found.show {
            display: block;
        }
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        .instructions h6 {
            color: #856404;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .instructions ol {
            margin: 0;
            padding-left: 20px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="welcome-card">
        <img src="{{ asset('Campfix/Images/logo.png') }}" alt="CampFix Logo" class="logo">
        
        <h2 class="welcome-title">Welcome to CampFix!</h2>
        <p class="welcome-subtitle">Enter your Student ID to get your login credentials</p>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> This is a temporary page to help you access your account while we process welcome emails.
        </div>

        <div class="search-box mt-4">
            <i class="fas fa-search"></i>
            <input type="text" 
                   id="studentIdInput" 
                   class="form-control" 
                   placeholder="Enter your Student ID (e.g., 202200372282)"
                   autocomplete="off">
        </div>

        <div class="credential-box" id="credentialBox">
            <div class="credential-item">
                <div class="credential-label">
                    <i class="fas fa-user me-1"></i> Full Name
                </div>
                <div class="credential-value">
                    <span id="userName"></span>
                </div>
            </div>

            <div class="credential-item">
                <div class="credential-label">
                    <i class="fas fa-envelope me-1"></i> Email Address
                </div>
                <div class="credential-value">
                    <span id="userEmail"></span>
                    <button class="copy-btn" onclick="copyToClipboard('userEmail', this)">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>

            <div class="credential-item">
                <div class="credential-label">
                    <i class="fas fa-key me-1"></i> Temporary Password
                </div>
                <div class="credential-value">
                    <span id="userPassword"></span>
                    <button class="copy-btn" onclick="copyToClipboard('userPassword', this)">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>

            <div class="instructions">
                <h6><i class="fas fa-lightbulb me-1"></i> Next Steps:</h6>
                <ol>
                    <li>Go to <a href="{{ url('/login') }}" target="_blank"><strong>{{ url('/login') }}</strong></a></li>
                    <li>Use your email and temporary password to log in</li>
                    <li>You'll be prompted to change your password on first login</li>
                </ol>
            </div>
        </div>

        <div class="not-found" id="notFound">
            <i class="fas fa-times-circle fa-3x mb-3"></i>
            <h5>Student ID Not Found</h5>
            <p>Please check your Student ID and try again. If the problem persists, contact support.</p>
        </div>
    </div>

    <script>
        const studentIdInput = document.getElementById('studentIdInput');
        const credentialBox = document.getElementById('credentialBox');
        const notFound = document.getElementById('notFound');

        // Search when user types
        let searchTimeout;
        studentIdInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchStudent(this.value.trim());
            }, 500);
        });

        // Search on Enter key
        studentIdInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                searchStudent(this.value.trim());
            }
        });

        async function searchStudent(studentId) {
            if (!studentId) {
                credentialBox.classList.remove('show');
                notFound.classList.remove('show');
                return;
            }

            try {
                const response = await fetch('/api/welcome-credentials/' + encodeURIComponent(studentId), {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });

                const data = await response.json();

                if (data.success && data.user) {
                    // Show credentials
                    document.getElementById('userName').textContent = data.user.name;
                    document.getElementById('userEmail').textContent = data.user.email;
                    document.getElementById('userPassword').textContent = data.user.password;

                    credentialBox.classList.add('show');
                    notFound.classList.remove('show');
                } else {
                    // Not found
                    credentialBox.classList.remove('show');
                    notFound.classList.add('show');
                }
            } catch (error) {
                console.error('Error fetching credentials:', error);
                credentialBox.classList.remove('show');
                notFound.classList.add('show');
            }
        }

        function copyToClipboard(elementId, button) {
            const text = document.getElementById(elementId).textContent;
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                button.classList.add('copied');

                setTimeout(() => {
                    button.innerHTML = originalHtml;
                    button.classList.remove('copied');
                }, 2000);
            });
        }
    </script>
</body>
</html>
