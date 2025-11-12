const views = {
    login: {
        title: 'Welcome Back',
        subtitle: 'We\'re so excited to see you again!'
    },
    signup: {
        title: 'Create an Account',
        subtitle: 'Join the community and start exploring!'
    }
};

const selectors = {
    loginForm: document.getElementById('loginForm'),
    signupForm: document.getElementById('signupForm'),
    loginPanel: document.getElementById('login-form'),
    signupPanel: document.getElementById('signup-form'),
    title: document.querySelector('.auth-title'),
    subtitle: document.querySelector('.auth-subtitle'),
    message: document.getElementById('auth-message'),
    passwordHint: document.getElementById('password-match'),
    password: document.getElementById('signup-password'),
    confirm: document.getElementById('signup-password-confirm')
};

const showMessage = (text, type = 'error') => {
    selectors.message.textContent = text;
    selectors.message.className = `auth-message ${type} show`;
    clearTimeout(showMessage.timer);
    showMessage.timer = setTimeout(() => {
        selectors.message.className = 'auth-message';
        selectors.message.textContent = '';
    }, 4000);
};

const toggleView = (view) => {
    const other = view === 'login' ? 'signup' : 'login';
    selectors[`${view}Panel`].classList.add('active');
    selectors[`${other}Panel`].classList.remove('active');
    selectors.title.textContent = views[view].title;
    selectors.subtitle.textContent = views[view].subtitle;
    selectors.message.className = 'auth-message';
    selectors.message.textContent = '';
    selectors[`${other}Form`].reset();
    if (view === 'login') {
        selectors.passwordHint.textContent = '';
        selectors.passwordHint.className = 'password-match';
    }
};

const updatePasswordHint = () => {
    const match = selectors.password.value === selectors.confirm.value;
    if (!selectors.confirm.value) {
        selectors.passwordHint.textContent = '';
        selectors.passwordHint.className = 'password-match';
        return;
    }
    selectors.passwordHint.textContent = match ? 'Passwords match' : 'Passwords do not match';
    selectors.passwordHint.className = `password-match ${match ? 'match' : 'no-match'}`;
};

const handleLogin = (event) => {
    event.preventDefault();
    const email = event.target['login-email'].value.trim();
    const password = event.target['login-password'].value;

    if (!email || !password) {
        showMessage('Please fill in all fields');
        return;
    }

    event.target.querySelector('.submit-btn').disabled = true;

    setTimeout(() => {
        localStorage.setItem('userToken', `mock-${Date.now()}`);
        localStorage.setItem('userId', '1');
        localStorage.setItem('username', email);
        showMessage('Login successful! Redirecting...', 'success');
        setTimeout(() => (window.location.href = 'index.html'), 800);
    }, 600);
};

const handleSignup = (event) => {
    event.preventDefault();
    const data = {
        name: event.target['signup-name'].value.trim(),
        email: event.target['signup-email'].value.trim(),
        username: event.target['signup-username'].value.trim(),
        password: selectors.password.value,
        confirm: selectors.confirm.value
    };

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (Object.values(data).some(value => !value)) {
        showMessage('Please fill in all fields');
        return;
    }

    if (!emailRegex.test(data.email)) {
        showMessage('Please enter a valid email address');
        return;
    }

    if (data.password.length < 6) {
        showMessage('Password must be at least 6 characters');
        return;
    }

    if (data.password !== data.confirm) {
        showMessage('Passwords do not match');
        return;
    }

    event.target.querySelector('.submit-btn').disabled = true;

    setTimeout(() => {
        localStorage.setItem('userToken', `mock-${Date.now()}`);
        localStorage.setItem('userId', Date.now().toString());
        localStorage.setItem('username', data.username);
        localStorage.setItem('userName', data.name);
        showMessage('Account created! Redirecting...', 'success');
        setTimeout(() => (window.location.href = 'index.html'), 800);
    }, 600);
};

document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('userToken')) {
        window.location.href = 'index.html';
        return;
    }

    document.querySelectorAll('[data-switch]').forEach((button) => {
        button.addEventListener('click', () => toggleView(button.dataset.switch));
    });

    if (selectors.password && selectors.confirm) {
        selectors.password.addEventListener('input', updatePasswordHint);
        selectors.confirm.addEventListener('input', updatePasswordHint);
    }

    selectors.loginForm.addEventListener('submit', handleLogin);
    selectors.signupForm.addEventListener('submit', handleSignup);
});
