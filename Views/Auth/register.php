<?php $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register - Twitch Clone</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <div class="box">
        <h1>Create Account</h1>
        
        <div id="registerMessages"></div>
        
        <form method="post" id="registerForm">
            <input name="username" id="reg_username" placeholder="Username"><br><br>
            <input name="password" id="reg_password" type="password" placeholder="Password">
            <div id="passwordStrength"></div><br>
            <input type="password" name="password2" id="reg_password2" placeholder="Confirm password"><br><br>
            <button type="submit" id="registerBtn">Register</button>
        </form>
        <p><a href="<?= $base_url ?>/login">Already have account?</a></p>
    </div>
</div>

<script src="assets/js/validation.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registerForm');
    const usernameInput = document.getElementById('reg_username');
    const passwordInput = document.getElementById('reg_password');
    const password2Input = document.getElementById('reg_password2');
    const registerBtn = document.getElementById('registerBtn');
    
    // Nettoyer les états d'erreur quand l'utilisateur retape
    usernameInput.addEventListener('input', () => {
        validator.setInputState('reg_username', '');
    });

    passwordInput.addEventListener('input', () => {
        validator.setInputState('reg_password', '');
        // Réactiver l'indicateur de force après nettoyage
        validator.showPasswordStrength('reg_password', 'passwordStrength');
    });

    password2Input.addEventListener('input', () => {
        validator.setInputState('reg_password2', '');
    });
    
    // Indicateur de force du mot de passe uniquement
    validator.showPasswordStrength('reg_password', 'passwordStrength');

    // Validation à la soumission uniquement
    form.addEventListener('submit', (e) => {
        e.preventDefault(); // Toujours empêcher la soumission par défaut
        
        let hasErrors = false;
        
        // Vérification des champs vides d'abord
        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();
        const password2 = password2Input.value.trim();
        
        if (!username) {
            validator.setInputState('reg_username', 'error');
            validator.shakeElement('reg_username');
            validator.showToast('Nom d\'utilisateur requis', 'error');
            hasErrors = true;
        }
        
        if (!password) {
            validator.setInputState('reg_password', 'error');
            validator.shakeElement('reg_password');
            if (!hasErrors) {
                validator.showToast('Mot de passe requis', 'error');
            }
            hasErrors = true;
        }
        
        if (!password2) {
            validator.setInputState('reg_password2', 'error');
            validator.shakeElement('reg_password2');
            if (!hasErrors) {
                validator.showToast('Confirmation du mot de passe requise', 'error');
            }
            hasErrors = true;
        }
        
        // Si pas vides, vérifier la validité
        if (!hasErrors) {
            const usernameResult = formValidators.validateUsername(username);
            const passwordResult = formValidators.validatePassword(password);
            const matchResult = formValidators.validatePasswordMatch(password, password2);
            
            if (!usernameResult.valid) {
                validator.setInputState('reg_username', 'error');
                validator.shakeElement('reg_username');
                validator.showToast(usernameResult.message, 'error');
                hasErrors = true;
            }
            
            if (!passwordResult.valid) {
                validator.setInputState('reg_password', 'error');
                validator.shakeElement('reg_password');
                if (!hasErrors) {
                    validator.showToast(passwordResult.message, 'error');
                }
                hasErrors = true;
            }
            
            if (!matchResult.valid) {
                validator.setInputState('reg_password2', 'error');
                validator.shakeElement('reg_password2');
                if (!hasErrors) {
                    validator.showToast(matchResult.message, 'error');
                }
                hasErrors = true;
            }
        }
        
        if (hasErrors) {
            registerBtn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                registerBtn.style.transform = 'scale(1)';
            }, 150);
        } else {
            // Si tout est valide, soumettre le formulaire normalement
            registerBtn.disabled = true;
            registerBtn.textContent = 'Création...';
            validator.showToast('Création du compte en cours...', 'info', 1000);
            setTimeout(() => {
                form.submit();
            }, 500);
        }
    });
});
</script>
</body>
</html>