<?php $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - Twitch Clone</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <div class="box">
        <h1>Twitch Clone</h1>
        
        <div id="loginMessages"></div>
        
        <form method="post" id="loginForm">
            <input name="username" id="username" placeholder="Username"><br><br>
            <input name="password" id="password" type="password" placeholder="Password"><br><br>
            <button type="submit" id="loginBtn">Login</button>
        </form>
        <p><a href="<?= $base_url ?>/register">Create account</a></p>
    </div>
</div>

<script src="assets/js/validation.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const loginBtn = document.getElementById('loginBtn');

    // Nettoyer les états d'erreur quand l'utilisateur retape
    usernameInput.addEventListener('input', () => {
        validator.setInputState('username', '');
    });

    passwordInput.addEventListener('input', () => {
        validator.setInputState('password', '');
    });

    // Validation à la soumission uniquement
    form.addEventListener('submit', (e) => {
        e.preventDefault(); // Toujours empêcher la soumission par défaut
        
        let hasErrors = false;
        
        // Vérification des champs vides d'abord
        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();
        
        if (!username) {
            validator.setInputState('username', 'error');
            validator.shakeElement('username');
            validator.showToast('Nom d\'utilisateur requis', 'error');
            hasErrors = true;
        }
        
        if (!password) {
            validator.setInputState('password', 'error');
            validator.shakeElement('password');
            if (!hasErrors) {
                validator.showToast('Mot de passe requis', 'error');
            }
            hasErrors = true;
        }
        
        // Si pas vides, vérifier la validité
        if (!hasErrors) {
            const usernameResult = formValidators.validateUsername(username);
            const passwordResult = formValidators.validatePassword(password);
            
            if (!usernameResult.valid) {
                validator.setInputState('username', 'error');
                validator.shakeElement('username');
                validator.showToast(usernameResult.message, 'error');
                hasErrors = true;
            }
            
            if (!passwordResult.valid) {
                validator.setInputState('password', 'error');
                validator.shakeElement('password');
                if (!hasErrors) {
                    validator.showToast(passwordResult.message, 'error');
                }
                hasErrors = true;
            }
        }
        
        if (hasErrors) {
            loginBtn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                loginBtn.style.transform = 'scale(1)';
            }, 150);
        } else {
            // Si tout est valide, soumettre le formulaire normalement
            loginBtn.disabled = true;
            loginBtn.textContent = 'Connexion...';
            form.submit();
        }
    });
});
</script>
</body>
</html>