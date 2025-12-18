<?php
session_start();
require_once __DIR__ . '/../../../Controller/AuthController.php';

$authController = new AuthController();

// If already logged in, redirect to feed
if ($authController->isLoggedIn() && !$authController->isAdmin()) {
    header('Location: ../feed/index.php');
    exit();
}

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'signup') {
            // Signup form submitted
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $fullName = trim($_POST['full_name'] ?? '');
            
            // Validation
            if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
                $message = 'All fields are required';
                $messageType = 'error';
            } elseif ($password !== $confirmPassword) {
                $message = 'Passwords do not match';
                $messageType = 'error';
            } elseif (strlen($password) < 6) {
                $message = 'Password must be at least 6 characters';
                $messageType = 'error';
            } else {
                $result = $authController->signup($username, $email, $password, $fullName);
                if ($result['success']) {
                    $message = $result['message'];
                    $messageType = 'success';
                    // Auto login after signup
                    $loginResult = $authController->login($email, $password);
                    if ($loginResult['success']) {
                        header('Location: ../feed/index.php');
                        exit();
                    }
                } else {
                    $message = $result['message'];
                    $messageType = 'error';
                }
            }
        } elseif ($_POST['action'] === 'login') {
            // Login form submitted
            $emailOrUsername = trim($_POST['email_or_username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($emailOrUsername) || empty($password)) {
                $message = 'Email/username and password are required';
                $messageType = 'error';
            } else {
                $result = $authController->login($emailOrUsername, $password);
                if ($result['success']) {
                    if ($result['is_admin']) {
                        // Admin login - redirect to back office
                        header('Location: ../../BackOffice/dashboard/index.html');
                        exit();
                    } else {
                        // Regular user - redirect to feed
                        header('Location: ../feed/index.php');
                        exit();
                    }
                } else {
                    $message = $result['message'];
                    $messageType = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Sign Up - STRIMR</title>
    <link rel="stylesheet" href="../login-styles.css">
    <style>
        .auth-message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        .auth-message.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        .auth-message.success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="bg-decoration"></div>
        
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Welcome to STRIMR</h1>
                <p class="auth-subtitle">Join our community or sign in to continue</p>
            </div>

            <!-- Message Display -->
            <?php if ($message): ?>
                <div id="auth-message" class="auth-message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- LOGIN FORM (Default View) -->
            <div id="login-form" class="auth-form active">
                <form id="loginForm" method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="login-email" class="form-label">
                            EMAIL OR USERNAME
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="login-email" 
                            name="email_or_username"
                            class="form-input"
                            placeholder="Enter your email or username"
                            required
                            autocomplete="username"
                        />
                    </div>

                    <div class="form-group">
                        <label for="login-password" class="form-label">
                            PASSWORD
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="login-password" 
                            name="password"
                            class="form-input"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        />
                        <a href="#" class="forgot-password">Forgot your password?</a>
                    </div>

                    <button type="submit" class="submit-btn">
                        <span class="btn-text">Log In</span>
                        <span class="btn-loader"></span>
                    </button>

                    <div class="auth-switch">
                        <span class="switch-text">Need an account?</span>
                        <button type="button" class="switch-link" data-switch="signup">
                            Register
                        </button>
                    </div>
                </form>
            </div>

            <!-- SIGNUP FORM (Hidden by default) -->
            <div id="signup-form" class="auth-form">
                <form id="signupForm" method="POST">
                    <input type="hidden" name="action" value="signup">
                    
                    <div class="form-group">
                        <label for="signup-name" class="form-label">
                            FULL NAME
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="signup-name" 
                            name="full_name"
                            class="form-input"
                            placeholder="Enter your full name"
                            required
                            autocomplete="name"
                        />
                    </div>

                    <div class="form-group">
                        <label for="signup-email" class="form-label">
                            EMAIL
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="signup-email" 
                            name="email"
                            class="form-input"
                            placeholder="Enter your email"
                            required
                            autocomplete="email"
                        />
                    </div>

                    <div class="form-group">
                        <label for="signup-username" class="form-label">
                            USERNAME
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="signup-username" 
                            name="username"
                            class="form-input"
                            placeholder="Choose a username"
                            required
                            autocomplete="username"
                            pattern="[a-zA-Z0-9_]{3,20}"
                            title="3-20 characters, letters, numbers, and underscores only"
                        />
                    </div>

                    <div class="form-group">
                        <label for="signup-password" class="form-label">
                            PASSWORD
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="signup-password" 
                            name="password"
                            class="form-input"
                            placeholder="Create a password (min. 6 characters)"
                            required
                            minlength="6"
                            autocomplete="new-password"
                        />
                    </div>

                    <div class="form-group">
                        <label for="signup-password-confirm" class="form-label">
                            CONFIRM PASSWORD
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="signup-password-confirm" 
                            name="confirm_password"
                            class="form-input"
                            placeholder="Confirm your password"
                            required
                            minlength="6"
                            autocomplete="new-password"
                        />
                        <span id="password-match" class="password-match"></span>
                    </div>

                    <button type="submit" class="submit-btn">
                        <span class="btn-text">Create Account</span>
                        <span class="btn-loader"></span>
                    </button>

                    <div class="auth-switch">
                        <span class="switch-text">Already have an account?</span>
                        <button type="button" class="switch-link" data-switch="login">
                            Log In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Form switching logic
        document.querySelectorAll('.switch-link').forEach(button => {
            button.addEventListener('click', function() {
                const target = this.getAttribute('data-switch');
                const loginForm = document.getElementById('login-form');
                const signupForm = document.getElementById('signup-form');
                
                if (target === 'signup') {
                    loginForm.classList.remove('active');
                    signupForm.classList.add('active');
                } else {
                    signupForm.classList.remove('active');
                    loginForm.classList.add('active');
                }
            });
        });

        // Password match validation
        const passwordInput = document.getElementById('signup-password');
        const confirmPasswordInput = document.getElementById('signup-password-confirm');
        const passwordMatch = document.getElementById('password-match');

        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value && passwordInput.value) {
                    if (this.value === passwordInput.value) {
                        passwordMatch.textContent = '✓ Passwords match';
                        passwordMatch.style.color = '#86efac';
                    } else {
                        passwordMatch.textContent = '✗ Passwords do not match';
                        passwordMatch.style.color = '#fca5a5';
                    }
                } else {
                    passwordMatch.textContent = '';
                }
            });
        }

        // Form validation
        document.getElementById('signupForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('signup-password').value;
            const confirmPassword = document.getElementById('signup-password-confirm').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters');
                return false;
            }
        });
    </script>
</body>
</html>
