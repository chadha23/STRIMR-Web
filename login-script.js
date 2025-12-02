var loginFormElement = document.getElementById('loginForm');
var signupFormElement = document.getElementById('signupForm');
var loginPanelElement = document.getElementById('login-form');
var signupPanelElement = document.getElementById('signup-form');
var titleElement = document.querySelector('.auth-title');
var subtitleElement = document.querySelector('.auth-subtitle');
var messageElement = document.getElementById('auth-message');
var passwordMessageElement = document.getElementById('password-match');
var signupPasswordElement = document.getElementById('signup-password');
var signupConfirmElement = document.getElementById('signup-password-confirm');
var messageTimerId = null;

function showMessage(text, type) {
    var className = 'auth-message show';
    if (type === 'success') {
        className += ' success';
    } else {
        className += ' error';
    }
    messageElement.textContent = text;
    messageElement.className = className;

    if (messageTimerId) {
        clearTimeout(messageTimerId);
    }
    messageTimerId = setTimeout(hideMessage, 4000);
}

function hideMessage() {
    messageElement.className = 'auth-message';
    messageElement.textContent = '';
}

function showLoginForm() {
    loginPanelElement.classList.add('active');
    signupPanelElement.classList.remove('active');
    titleElement.textContent = 'Welcome Back';
    subtitleElement.textContent = 'We\'re so excited to see you again!';
    signupFormElement.reset();
    passwordMessageElement.textContent = '';
    passwordMessageElement.className = 'password-match';
    hideMessage();
}

function showSignupForm() {
    signupPanelElement.classList.add('active');
    loginPanelElement.classList.remove('active');
    titleElement.textContent = 'Create an Account';
    subtitleElement.textContent = 'Join the community and start exploring!';
    loginFormElement.reset();
    hideMessage();
}

function updatePasswordMessage() {
    var passwordText = signupPasswordElement.value;
    var confirmText = signupConfirmElement.value;

    if (confirmText === '') {
        passwordMessageElement.textContent = '';
        passwordMessageElement.className = 'password-match';
        return;
    }

    if (passwordText === confirmText) {
        passwordMessageElement.textContent = 'Passwords match';
        passwordMessageElement.className = 'password-match match';
    } else {
        passwordMessageElement.textContent = 'Passwords do not match';
        passwordMessageElement.className = 'password-match no-match';
    }
}

function submitLogin(event) {
    event.preventDefault();

    var emailValue = document.getElementById('login-email').value.trim();
    var passwordValue = document.getElementById('login-password').value;
    var submitButton = loginFormElement.querySelector('.submit-btn');

    if (emailValue === '' || passwordValue === '') {
        showMessage('Please fill in all fields');
        return;
    }

    submitButton.disabled = true;

    setTimeout(function () {
        localStorage.setItem('userToken', 'mock-' + Date.now());
        localStorage.setItem('userId', '1');
        localStorage.setItem('username', emailValue);
        showMessage('Login successful! Redirecting...', 'success');

        setTimeout(function () {
            window.location.href = 'index.html';
        }, 800);
    }, 600);
}

function submitSignup(event) {
    event.preventDefault();

    var fullNameValue = document.getElementById('signup-name').value.trim();
    var emailValue = document.getElementById('signup-email').value.trim();
    var usernameValue = document.getElementById('signup-username').value.trim();
    var passwordValue = signupPasswordElement.value;
    var confirmValue = signupConfirmElement.value;
    var submitButton = signupFormElement.querySelector('.submit-btn');

    if (fullNameValue === '' || emailValue === '' || usernameValue === '' || passwordValue === '' || confirmValue === '') {
        showMessage('Please fill in all fields');
        return;
    }

    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(emailValue)) {
        showMessage('Please enter a valid email address');
        return;
    }

    if (passwordValue.length < 6) {
        showMessage('Password must be at least 6 characters');
        return;
    }

    if (passwordValue !== confirmValue) {
        showMessage('Passwords do not match');
        return;
    }

    submitButton.disabled = true;

    setTimeout(function () {
        localStorage.setItem('userToken', 'mock-' + Date.now());
        localStorage.setItem('userId', Date.now().toString());
        localStorage.setItem('username', usernameValue);
        localStorage.setItem('userName', fullNameValue);
        showMessage('Account created! Redirecting...', 'success');

        setTimeout(function () {
            window.location.href = 'index.html';
        }, 800);
    }, 600);
}

function setUpLoginPage() {
    if (localStorage.getItem('userToken')) {
        window.location.href = 'index.html';
        return;
    }

    showLoginForm();
}

setUpLoginPage();
