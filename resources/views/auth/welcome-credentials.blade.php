<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        .user-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            display: none;
            text-align: center;
        }
        .user-box.show {
            display: block;
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .user-info {
            margin-bottom: 20px;
        }
        .user-name {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        .user-email {
            color: #666;
            font-size: 16px;
        }
        .send-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .send-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
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
        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            display: none;
        }
        .success-message.show {
            display: block;
            animation: fadeIn 0.3s;
        }
        .success-message i {
            font-size: 48px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="welcome-card">
        <img src="{{ asset('Campfix/Images/logo.png') }}" alt="CampFix Logo" class="logo">
        
        <h2 class="welcome-title">Welcome to CampFix!</h2>
        <p class="welcome-subtitle">Enter your Student ID to receive your login credentials</p>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>How it works:</strong> Enter your Student ID and we'll send your login credentials to your email.
        </div>

        <div class="search-box mt-4">
            <i class="fas fa-id-card"></i>
            <input type="text" 
                   id="studentIdInput" 
                   class="form-control" 
                   placeholder="Enter your Student ID (e.g., 202200372282)"
                   autocomplete="off">
        </div>

        <div class="user-box" id="userBox">
            <div class="user-info">
                <div class="user-name" id="userName"></div>
                <div class="user-email" id="userEmail"></div>
            </div>

            <button class="send-btn" id="sendBtn" onclick="sendCredentials()">
                <i class="fas fa-paper-plane me-2"></i> Send Credentials to My Email
            </button>

            <p class="text-muted mt-3 mb-0" style="font-size: 14px;">
                Your email and temporary password will be sent to the email address above.
            </p>
        </div>

        <div class="success-message" id="successMessage">
            <i class="fas fa-check-circle text-success"></i>
            <h5>Credentials Sent!</h5>
            <p class="mb-0">Please check your email for your login credentials. You can now <a href="{{ url('/login') }}"><strong>log in here</strong></a>.</p>
        </div>

        <div class="not-found" id="notFound">
            <i class="fas fa-times-circle fa-3x mb-3"></i>
            <h5>Student ID Not Found</h5>
            <p>Please check your Student ID and try again. If the problem persists, contact support.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const studentIdInput = document.getElementById('studentIdInput');
        const userBox = document.getElementById('userBox');
        const notFound = document.getElementById('notFound');
        const successMessage = document.getElementById('successMessage');
        let currentUserId = null;

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
                userBox.classList.remove('show');
                notFound.classList.remove('show');
                successMessage.classList.remove('show');
                return;
            }

            try {
                const response = await fetch('/api/welcome-credentials/search/' + encodeURIComponent(studentId), {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success && data.user) {
                    // Show user info
                    document.getElementById('userName').textContent = data.user.name;
                    document.getElementById('userEmail').textContent = data.user.email;
                    currentUserId = data.user.id;

                    userBox.classList.add('show');
                    notFound.classList.remove('show');
                    successMessage.classList.remove('show');
                } else {
                    // Not found
                    userBox.classList.remove('show');
                    notFound.classList.add('show');
                    successMessage.classList.remove('show');
                    currentUserId = null;
                }
            } catch (error) {
                console.error('Error searching student:', error);
                userBox.classList.remove('show');
                notFound.classList.add('show');
                successMessage.classList.remove('show');
                currentUserId = null;
            }
        }

        async function sendCredentials() {
            if (!currentUserId) return;

            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending...';

            try {
                const response = await fetch('/api/welcome-credentials/send/' + currentUserId, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    userBox.classList.remove('show');
                    successMessage.classList.add('show');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Credentials Sent!',
                        text: 'Your login credentials have been sent to your email.',
                        confirmButtonColor: '#667eea',
                        timer: 3000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Send',
                        text: data.message || 'Could not send credentials. Please try again.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            } catch (error) {
                console.error('Error sending credentials:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
            } finally {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Send Credentials to My Email';
            }
        }
    </script>
</body>
</html>
