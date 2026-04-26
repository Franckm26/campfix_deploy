<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Security Headers (OWASP A6: Security Misconfiguration) -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>CampFix - Campus Facility Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/"><img src="{{ asset('Campfix/Images/logo.png') }}" alt="CampFix Logo" height="60"><span class="logo-text"><span class="camp-text">Camp</span><span class="fix-text">fix</span></span></a>
            <button class="mobile-menu-btn" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="d-flex align-items-center gap-3 nav-links" id="navLinks">
                <a href="/" class="nav-link">Home</a>
                <a href="#features" class="nav-link">Features</a>
                <a href="#how-it-works" class="nav-link">How It Works</a>
                @auth
                    <a href="/dashboard" class="btn-login">Dashboard</a>
                @else
                    <a href="javascript:void(0)" class="btn-login" onclick="openLoginModal(event); return false;">Login</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Carousel -->
    <section class="hero-carousel-section">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <!-- Slides -->
            <div class="carousel-inner">
                <!-- Slide 1 -->
                <div class="carousel-item active">
                    <div class="carousel-slide slide-1">
                        <div class="carousel-overlay"></div>
                        <div class="carousel-content">
                            <p class="carousel-tagline">CAMPUS FACILITY MANAGEMENT</p>
                            <h1 class="carousel-title">Campus Facility<br>Management <span>Simplified</span></h1>
                            <p class="carousel-desc">CampFix is a centralized web-based platform for campus facility requests, concern reporting, and data-driven decision support for STI College Novaliches.</p>
                            <div class="carousel-btns">
                                <a href="javascript:void(0)" class="btn-carousel-primary" onclick="openLoginModal(event); return false;">Get Started</a>
                                <a href="#features" class="btn-carousel-secondary">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Slide 2 -->
                <div class="carousel-item">
                    <div class="carousel-slide slide-2">
                        <div class="carousel-overlay"></div>
                        <div class="carousel-content">
                            <p class="carousel-tagline">REPORT & TRACK</p>
                            <h1 class="carousel-title">Submit Concerns<br><span>Instantly</span></h1>
                            <p class="carousel-desc">Report facility issues with photos, location, and details. Track every request from submission to resolution in real time.</p>
                            <div class="carousel-btns">
                                <a href="javascript:void(0)" class="btn-carousel-primary" onclick="openLoginModal(event); return false;">Report Now</a>
                                <a href="#how-it-works" class="btn-carousel-secondary">How It Works</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Slide 3 -->
                <div class="carousel-item">
                    <div class="carousel-slide slide-3">
                        <div class="carousel-overlay"></div>
                        <div class="carousel-content">
                            <p class="carousel-tagline">DATA-DRIVEN DECISIONS</p>
                            <h1 class="carousel-title">Smarter Campus<br><span>Management</span></h1>
                            <p class="carousel-desc">Leverage analytics and audit logs to identify recurring issues, optimize response times, and improve campus facilities for everyone.</p>
                            <div class="carousel-btns">
                                <a href="javascript:void(0)" class="btn-carousel-primary" onclick="openLoginModal(event); return false;">Get Started</a>
                                <a href="#features" class="btn-carousel-secondary">Explore Features</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="carousel-controls">
                <button class="carousel-ctrl-btn" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-ctrl-btn pause-btn" id="carouselPauseBtn" onclick="toggleCarousel()">
                    <i class="fas fa-pause" id="pauseIcon"></i>
                </button>
                <button class="carousel-ctrl-btn" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- Indicators -->
            <div class="carousel-indicators-custom">
                <button class="indicator active" data-bs-target="#heroCarousel" data-bs-slide-to="0"></button>
                <button class="indicator" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                <button class="indicator" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Powerful Features</h2>
                <p>Everything you need to manage campus facilities efficiently</p>
            </div>
            <div class="row g-5 justify-content-center">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="feature-card-new">
                        <div class="feature-illustration">
                            <div class="feat-illus-bg feat-illus-bg-1">
                                <i class="fas fa-clipboard-list feat-illus-icon"></i>
                            </div>
                        </div>
                        <h3>Easy Request Submission</h3>
                        <p>Students, faculty, and staff can submit facility concerns with location, description, and optional images with just a few taps.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="feature-card-new">
                        <div class="feature-illustration">
                            <div class="feat-illus-bg feat-illus-bg-2">
                                <i class="fas fa-chart-line feat-illus-icon"></i>
                            </div>
                        </div>
                        <h3>Data Analytics</h3>
                        <p>Generate insights on recurring issues, response times, and resource utilization for smarter, data-driven decisions.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="feature-card-new">
                        <div class="feature-illustration">
                            <div class="feat-illus-bg feat-illus-bg-3">
                                <i class="fas fa-search feat-illus-icon"></i>
                            </div>
                        </div>
                        <h3>Track Progress</h3>
                        <p>Monitor request status in real-time with clear Pending, In Progress, and Resolved indicators at every step.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="feature-card-new">
                        <div class="feature-illustration">
                            <div class="feat-illus-bg feat-illus-bg-4">
                                <i class="fas fa-bell feat-illus-icon"></i>
                            </div>
                        </div>
                        <h3>Real-Time Notifications</h3>
                        <p>Stay informed with instant status updates and notifications on your facility requests as they progress.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>How It Works</h2>
                <p>Simple steps to get your facility concerns addressed</p>
            </div>
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h4>Login</h4>
                        <p>Sign in with institutional credentials</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4>Submit Request</h4>
                        <p>Fill out concern form with details</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4>Track Status</h4>
                        <p>Monitor progress through updates</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h4>Get Resolution</h4>
                        <p>Receive notification when resolved</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Improve Campus Facilities?</h2>
            <p>Join STI College Novaliches in transforming facility management</p>
            <a href="javascript:void(0)" class="btn-white" onclick="openLoginModal(event); return false;">Get Started Now</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="footer-brand">Camp<span>Fix</span></div>
                    <p>A centralized web-based platform for campus facility requests, concern reporting, and data-driven decision support.</p>
                </div>
                <div class="col-md-2 mb-4">
                    <div class="footer-links">
                        <h5>Quick Links</h5>
                        <a href="/">Home</a>
                        <a href="#features">Features</a>
                        <a href="#how-it-works">How It Works</a>
                    </div>
                </div>
                
               
                <div class="col-md-2 mb-4">
                    <div class="footer-links">
                        <h5>Contact</h5>
                        <a href="#">Support</a>
                        <a href="#">Help Desk</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 CampFix - STI College Novaliches. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="login-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; padding: 20px; box-sizing: border-box;">
        <div style="background: white; border-radius: 16px; padding: 30px; width: 100%; max-width: 400px; max-height: 90vh; overflow-y: auto; position: relative; margin: auto;">
            <button onclick="closeLoginModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">&times;</button>
            <h3 style="text-align: center; margin-bottom: 10px; color: #1e293b;">Welcome to CampFix</h3>
            <p style="text-align: center; color: #64748b; margin-bottom: 25px;">Login to continue</p>
            
            <!-- Validation Error Display (OWASP A7: Generic error messages) -->
            @if($errors->any())
            <div id="login-error" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 18px; font-size: 14px;">
                Invalid email or password.
            </div>
            @endif
            
            @if(session('error'))
            <div id="login-error-session" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 18px; font-size: 14px;">
                {{ session('error') }}
            </div>
            @endif
            
            <form id="login-form" method="POST" action="/login" novalidate>
                @csrf
                <div style="margin-bottom: 18px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Email</label>
                    <input type="email" name="email" id="login-email" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;" placeholder="Enter your email">
                    <small id="login-email-error" style="color: #dc2626; font-size: 12px; display: none;"></small>
                </div>
                <div style="margin-bottom: 18px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Password</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="login-password" required style="width: 100%; padding: 12px 42px 12px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; box-sizing: border-box;" placeholder="Enter your password">
                        <button type="button" onclick="toggleLoginPassword()" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; padding: 0;">
                            <i id="login-eye-icon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small id="login-password-error" style="color: #dc2626; font-size: 12px; display: none;"></small>
                </div>
                <button type="submit" id="login-submit" style="width: 100%; padding: 14px; background: #1e293b; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer;">Login</button>
            </form>
        </div>
    </div>

    <script>
        function toggleLoginPassword() {
            const input = document.getElementById('login-password');
            const icon = document.getElementById('login-eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('show');
        }
        
        // Open login modal function
        function openLoginModal(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const modal = document.getElementById('login-modal');
            if (modal) {
                modal.style.display = 'flex';
            }
        }
        
        // Close login modal function
        function closeLoginModal() {
            const modal = document.getElementById('login-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        }
        
        // Close login modal when clicking outside
        document.addEventListener('click', function(event) {
            const loginModal = document.getElementById('login-modal');
            const modalContent = loginModal?.querySelector('div[style*="border-radius"]');
            if (loginModal && loginModal.style.display === 'flex' && modalContent) {
                if (!modalContent.contains(event.target) && event.target !== loginModal) {
                    loginModal.style.display = 'none';
                }
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeLoginModal();
            }
        });
        
        // Login modal validation
        const loginForm = document.getElementById('login-form');
        const loginEmail = document.getElementById('login-email');
        const loginPassword = document.getElementById('login-password');
        const loginEmailError = document.getElementById('login-email-error');
        const loginPasswordError = document.getElementById('login-password-error');
        
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        function showError(element, errorElement, message) {
            element.style.borderColor = '#dc2626';
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
        
        function clearError(element, errorElement) {
            element.style.borderColor = '#e2e8f0';
            errorElement.style.display = 'none';
        }
        
        if (loginEmail) {
            loginEmail.addEventListener('blur', function() {
                const value = this.value.trim();
                if (!value) {
                    showError(this, loginEmailError, 'Email is required');
                } else if (!validateEmail(value)) {
                    showError(this, loginEmailError, 'Please enter a valid email address');
                } else {
                    clearError(this, loginEmailError);
                }
            });
            
            loginEmail.addEventListener('input', function() {
                if (this.value.trim() && validateEmail(this.value.trim())) {
                    clearError(this, loginEmailError);
                }
            });
        }
        
        if (loginPassword) {
            loginPassword.addEventListener('blur', function() {
                const value = this.value;
                if (!value) {
                    showError(this, loginPasswordError, 'Password is required');
                } else {
                    clearError(this, loginPasswordError);
                }
            });
            
            loginPassword.addEventListener('input', function() {
                if (this.value) {
                    clearError(this, loginPasswordError);
                }
            });
        }
        
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validate email
                const emailValue = loginEmail.value.trim();
                if (!emailValue) {
                    showError(loginEmail, loginEmailError, 'Email is required');
                    isValid = false;
                } else if (!validateEmail(emailValue)) {
                    showError(loginEmail, loginEmailError, 'Please enter a valid email address');
                    isValid = false;
                }
                
                // Validate password
                if (!loginPassword.value) {
                    showError(loginPassword, loginPasswordError, 'Password is required');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        }
        
        // Auto-show modal if there are validation errors
        @if($errors->any() || session('error'))
        document.addEventListener('DOMContentLoaded', function() {
            openLoginModal();
        });
        @endif

        // Carousel pause/play toggle
        let carouselPaused = false;
        function toggleCarousel() {
            const carousel = document.getElementById('heroCarousel');
            const icon = document.getElementById('pauseIcon');
            const bsCarousel = bootstrap.Carousel.getOrCreateInstance(carousel);
            if (carouselPaused) {
                bsCarousel.cycle();
                icon.classList.replace('fa-play', 'fa-pause');
                carouselPaused = false;
            } else {
                bsCarousel.pause();
                icon.classList.replace('fa-pause', 'fa-play');
                carouselPaused = true;
            }
        }

        // Sync indicator active state
        document.getElementById('heroCarousel').addEventListener('slide.bs.carousel', function (e) {
            document.querySelectorAll('.indicator').forEach((el, i) => {
                el.classList.toggle('active', i === e.to);
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
