/* ============================================
   LOGIN PAGE JAVASCRIPT
   ============================================
   Handles login, signup, and form validation
   ============================================ */

// DEBUG: Verify script is loading
console.log('=== login-script.js LOADED ===');

// ============================================
// FORM SWITCHING
// ============================================

/**
 * switchToSignup()
 * Switches from login form to signup form
 * 
 * Called when: User clicks "Register" link
 */
function switchToSignup() {
    // Hide login form
    document.getElementById('login-form').classList.remove('active');
    
    // Show signup form
    document.getElementById('signup-form').classList.add('active');
    
    // Update header
    document.querySelector('.auth-title').textContent = 'Create an Account';
    document.querySelector('.auth-subtitle').textContent = 'We\'re excited to have you join us!';
    
    // Clear any messages
    hideMessage();
    
    // Clear login form
    document.getElementById('loginForm').reset();
}

/**
 * switchToLogin()
 * Switches from signup form to login form
 * 
 * Called when: User clicks "Log In" link
 */
function switchToLogin() {
    // Hide signup form
    document.getElementById('signup-form').classList.remove('active');
    
    // Show login form
    document.getElementById('login-form').classList.add('active');
    
    // Update header
    document.querySelector('.auth-title').textContent = 'Welcome Back';
    document.querySelector('.auth-subtitle').textContent = 'We\'re so excited to see you again!';
    
    // Clear any messages
    hideMessage();
    
    // Clear signup form
    document.getElementById('signupForm').reset();
    document.getElementById('password-match').textContent = '';
    document.getElementById('password-match').className = 'password-match';
}

// ============================================
// PASSWORD VALIDATION
// ============================================

/**
 * Check if passwords match in signup form
 */
function checkPasswordMatch() {
    const password = document.getElementById('signup-password').value;
    const confirmPassword = document.getElementById('signup-password-confirm').value;
    const matchIndicator = document.getElementById('password-match');
    
    if (confirmPassword.length === 0) {
        matchIndicator.textContent = '';
        matchIndicator.className = 'password-match';
        return;
    }
    
    if (password === confirmPassword && password.length >= 6) {
        matchIndicator.textContent = '✓ Passwords match';
        matchIndicator.className = 'password-match match';
        return true;
    } else {
        matchIndicator.textContent = '✗ Passwords do not match';
        matchIndicator.className = 'password-match no-match';
        return false;
    }
}

// Add event listeners for password matching
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('signup-password');
    const confirmInput = document.getElementById('signup-password-confirm');
    
    if (passwordInput && confirmInput) {
        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmInput.addEventListener('input', checkPasswordMatch);
    }
    
    // CRITICAL: Attach form submit handler directly
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        console.log('Login form found, attaching submit handler');
        
        // Remove any existing onsubmit to avoid conflicts
        loginForm.onsubmit = null;
        
        // Attach submit handler - this will catch the form submission
        loginForm.addEventListener('submit', function(e) {
            console.log('=== FORM SUBMIT EVENT CAUGHT ===');
            // CRITICAL: Prevent default FIRST - before anything else
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            console.log('Default prevented, calling handleLogin');
            handleLogin(e);
            return false; // Extra safety - MUST return false
        }, true); // Use capture phase to catch early
        
        // Also set onsubmit directly as backup
        loginForm.onsubmit = function(e) {
            console.log('onsubmit handler called');
            e.preventDefault();
            e.stopPropagation();
            handleLogin(e);
            return false;
        };
        
        console.log('Form handlers attached successfully');
    } else {
        console.error('Login form not found!');
    }
});

// ============================================
// MESSAGE DISPLAY
// ============================================

/**
 * showMessage(text, type)
 * Shows error or success message
 * 
 * @param {string} text - Message text
 * @param {string} type - 'error' or 'success'
 */
function showMessage(text, type = 'error') {
    const messageDiv = document.getElementById('auth-message');
    messageDiv.textContent = text;
    messageDiv.className = `auth-message ${type} show`;
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        hideMessage();
    }, 5000);
}

/**
 * hideMessage()
 * Hides the message
 */
function hideMessage() {
    const messageDiv = document.getElementById('auth-message');
    messageDiv.classList.remove('show');
    messageDiv.textContent = '';
}

// ============================================
// LOGIN HANDLER
// ============================================

/**
 * handleLogin(event)
 * Handles login form submission
 * 
 * @param {Event} event - Form submit event
 */
function handleLogin(event) {
    // DEBUG: Log that function was called
    alert("handleLogin() CALLED");
    console.log('=== handleLogin CALLED ===');
    console.log('Event:', event);
    
    // CRITICAL: Prevent form from submitting normally
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }
    
    console.log('Form submission prevented');
    
    // Get form elements directly by ID to avoid any selector issues
    // If called from button click, we need to get the form
    const emailInput = document.getElementById('login-email');
    const passwordInput = document.getElementById('login-password');
    const submitBtn = document.querySelector('#loginForm .submit-btn');
    
    if (!emailInput || !passwordInput) {
        showMessage('Form elements not found. Please refresh the page.', 'error');
        return;
    }
    
    // Get values and trim whitespace
    const email = emailInput.value.trim();
    const password = passwordInput.value;
    
    // Basic validation - check for empty or suspicious values
    if (!email || !password) {
        showMessage('Please fill in all fields', 'error');
        return;
    }
    
    // Additional validation - check for minimum length
    if (email.length < 3) {
        showMessage('Please enter a valid email or username', 'error');
        emailInput.focus();
        return;
    }
    
    if (password.length < 1) {
        showMessage('Please enter your password', 'error');
        passwordInput.focus();
        return;
    }
    
    // Prevent double submission
    if (submitBtn && submitBtn.disabled) {
        return;
    }
    
    // Show loading state
    if (submitBtn) {
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    }
    
    // Clear any previous error messages
    hideMessage();
    
    // CRITICAL: Clear any existing tokens BEFORE attempting login
    // This prevents using stale/invalid tokens from previous sessions
    localStorage.removeItem('userToken');
    localStorage.removeItem('userId');
    localStorage.removeItem('username');
    localStorage.removeItem('isAdmin');
    
    // Send to PHP backend
    fetch('login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            email: email,
            password: password
        })
    })
    .then(response => {
        // Get response text first
        return response.text().then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                // If JSON parsing fails, reject the promise
                throw new Error('Invalid server response');
            }
            return {
                ok: response.ok,
                status: response.status,
                data: data
            };
        });
    })
    .then(result => {
        // DEBUG: Log the result to console
        console.log('=== LOGIN RESPONSE DEBUG ===');
        console.log('Full result:', result);
        console.log('Response OK:', result.ok);
        console.log('Response status:', result.status);
        console.log('Response data:', result.data);
        console.log('Success value:', result.data ? result.data.success : 'no data');
        console.log('User data:', result.data ? result.data.user : 'no user');
        
        // CRITICAL: Check each condition separately and fail early
        if (result.ok !== true) {
            console.error('FAIL: Response not OK. Status:', result.status);
            showMessage('Login failed. Invalid credentials.', 'error');
            if (submitBtn) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
            passwordInput.value = '';
            passwordInput.focus();
            localStorage.removeItem('userToken');
            localStorage.removeItem('userId');
            localStorage.removeItem('username');
            localStorage.removeItem('isAdmin');
            return; // STOP HERE - DO NOT CONTINUE
        }
        
        if (!result.data) {
            console.error('FAIL: No data in response');
            showMessage('Invalid server response.', 'error');
            if (submitBtn) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
            passwordInput.value = '';
            passwordInput.focus();
            localStorage.removeItem('userToken');
            localStorage.removeItem('userId');
            localStorage.removeItem('username');
            localStorage.removeItem('isAdmin');
            return; // STOP HERE
        }
        
        if (result.data.success !== true) {
            console.error('FAIL: success is not true. Value:', result.data.success);
            const errorMsg = result.data.error || result.data.message || 'Invalid email/username or password';
            showMessage(errorMsg, 'error');
            if (submitBtn) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
            passwordInput.value = '';
            passwordInput.focus();
            localStorage.removeItem('userToken');
            localStorage.removeItem('userId');
            localStorage.removeItem('username');
            localStorage.removeItem('isAdmin');
            return; // STOP HERE
        }
        
        if (!result.data.user || typeof result.data.user.id === 'undefined') {
            console.error('FAIL: No valid user data');
            showMessage('Invalid user data received.', 'error');
            if (submitBtn) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
            passwordInput.value = '';
            passwordInput.focus();
            localStorage.removeItem('userToken');
            localStorage.removeItem('userId');
            localStorage.removeItem('username');
            localStorage.removeItem('isAdmin');
            return; // STOP HERE
        }
        
        // ALL CHECKS PASSED - Login is successful
        console.log('SUCCESS: All checks passed. Storing token and redirecting.');
        
        // Store user session/token ONLY if login was successful
        localStorage.setItem('userToken', result.data.token);
        localStorage.setItem('userId', result.data.user.id);
        localStorage.setItem('username', result.data.user.username);
        
        // Save admin status (tolerant to 1, "1", true, "true")
        const isAdminFlag =
        result.data.isAdmin === true ||
        result.data.isAdmin === 1 ||
        result.data.isAdmin === '1' ||
        result.data.isAdmin === 'true';
        
        if (isAdminFlag) {
            localStorage.setItem('isAdmin', 'true');
        } else {
            localStorage.removeItem('isAdmin');
        }

        
        // Show success message
        showMessage('Login successful! Redirecting...', 'success');
        
        // Redirect based on admin status
        setTimeout(() => {
            if (result.data.isAdmin === true) {
                window.location.href = 'admin.html'; // Redirect admin to dashboard
            } else {
                window.location.href = 'index.html'; // Redirect normal user to main app
            }
        }, 500);
        
        // OLD CODE REMOVED - Using early returns instead
        /*
        } else {
        */
    })
    .catch(error => {
        console.error('Login error:', error);
        console.log('Login FAILED due to error - NOT redirecting');
        // On ANY error (network, JSON parse, etc.) - REJECT login
        showMessage('Connection error. Please check your internet connection and try again.', 'error');
        if (submitBtn) {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
        // Clear password field on error
        passwordInput.value = '';
        passwordInput.focus();
        
        // ABSOLUTELY DO NOT store tokens or redirect on error
        // Make sure no tokens are stored
        localStorage.removeItem('userToken');
        localStorage.removeItem('userId');
        localStorage.removeItem('username');
        localStorage.removeItem('isAdmin');
    });
}

// ============================================
// SIGNUP HANDLER
// ============================================

/**
 * handleSignup(event)
 * Handles signup form submission
 * 
 * @param {Event} event - Form submit event
 */
function handleSignup(event) {
    event.preventDefault();
    
    const name = document.getElementById('signup-name').value.trim();
    const email = document.getElementById('signup-email').value.trim();
    const username = document.getElementById('signup-username').value.trim();
    const password = document.getElementById('signup-password').value;
    const confirmPassword = document.getElementById('signup-password-confirm').value;
    
    // Basic validation
    if (!name || !email || !username || !password || !confirmPassword) {
        showMessage('Please fill in all fields', 'error');
        return;
    }
    
    // Check password length
    if (password.length < 6) {
        showMessage('Password must be at least 6 characters', 'error');
        return;
    }
    
    // Check password match
    if (password !== confirmPassword) {
        showMessage('Passwords do not match', 'error');
        return;
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showMessage('Please enter a valid email address', 'error');
        return;
    }
    
    // Show loading state
    const form = document.getElementById('signupForm');
    const submitBtn = form ? form.querySelector('.submit-btn') : null;
    if (submitBtn) {
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    }
    
    // Prepare payload - backend will store default password "123"
    const payload = {
        name: name,
        email: email,
        username: username
    };
    
    fetch('signup.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => {
        return response.text().then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (error) {
                throw new Error('Invalid server response');
            }
            return {
                ok: response.ok,
                status: response.status,
                data: data
            };
        });
    })
    .then(result => {
        console.log('=== SIGNUP RESPONSE DEBUG ===', result);
        
        if (result.ok && result.data && result.data.success) {
            showMessage('Account created! You can now log in with password 123.', 'success');
            document.getElementById('signupForm').reset();
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            
            // Switch back to login after a short delay
            setTimeout(() => {
                switchToLogin();
                document.getElementById('login-email').value = email;
            }, 1200);
        } else {
            const errorMsg = (result.data && result.data.error) || 'Signup failed. Please try again.';
            showMessage(errorMsg, 'error');
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Signup error:', error);
        showMessage('Connection error. Please try again.', 'error');
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
    });
}

// ============================================
// INITIALIZATION
// ============================================

/**
 * Check if user is already logged in
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== LOGIN PAGE LOADED ===');
    
    // Check if user has a token (already logged in)
    const token = localStorage.getItem('userToken');
    console.log('Token in localStorage:', token);
    
    if (token) {
        // User is already logged in, redirect to appropriate page
        console.log('Token found, checking user data...');
        // BUT FIRST - validate the token by checking if user data exists
        const userId = localStorage.getItem('userId');
        const username = localStorage.getItem('username');
        
        if (!userId || !username) {
            // Invalid token - clear it
            console.log('Invalid token data, clearing...');
            localStorage.removeItem('userToken');
            localStorage.removeItem('userId');
            localStorage.removeItem('username');
            localStorage.removeItem('isAdmin');
            return; // Don't redirect
        }
        
        // Check admin status from localStorage
        const isAdmin = localStorage.getItem('isAdmin') === 'true';
        
        if (isAdmin) {
            console.log('Admin user detected, redirecting to admin.html');
            window.location.href = 'admin.html'; // Redirect admin to dashboard
        } else {
            console.log('Normal user detected, redirecting to index.html');
            window.location.href = 'index.html'; // Redirect normal user to main app
        }
        return;
    } else {
        console.log('No token found, staying on login page');
    }
    
    // Clear any autofilled values that might cause issues
    const emailInput = document.getElementById('login-email');
    const passwordInput = document.getElementById('login-password');
    
    if (emailInput && emailInput.value) {
        // Only clear if it looks like random characters (very short or suspicious)
        const value = emailInput.value.trim();
        if (value.length < 3 || !value.includes('@') && value.length < 2) {
            emailInput.value = '';
        }
    }
    
    if (passwordInput && passwordInput.value) {
        // Clear password field on load to prevent autofill issues
        passwordInput.value = '';
    }
    
    // Focus on email input for better UX
    if (emailInput) {
        setTimeout(() => {
            emailInput.focus();
        }, 100);
    }
});



