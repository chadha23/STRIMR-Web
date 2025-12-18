<?php $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Acheter des Points - STRIMR</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .points-packages {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .package {
            background: #18181b;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
        }
        .package:hover, .package.selected {
            border-color: #9147ff;
            background: #1f1f23;
        }
        .package-points {
            font-size: 32px;
            font-weight: bold;
            color: #9147ff;
            margin-bottom: 10px;
        }
        .package-price {
            font-size: 24px;
            color: #51cf66;
        }
        .bonus {
            color: #ffd43b;
            font-weight: bold;
            margin-top: 10px;
        }
        .card-form {
            background: #18181b;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
        }
        .card-input {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #444;
            border-radius: 8px;
            background: #2c2c2c;
            color: white;
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="container">
    <div style="text-align:right;padding:20px;">
        <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> | 
        <a href="<?= $base_url ?>/profile">Profil</a> | 
        <a href="<?= $base_url ?>/dashboard">Dashboard</a>
    </div>

    <h1 style="text-align:center;color:#9147ff;">Acheter des Points</h1>

    <div style="text-align:center;margin:20px 0;">
        <strong>Points actuels : <?= number_format($currentPoints) ?> 💎</strong>
    </div>

    <?php if ($error): ?>
        <div style="background:#ff6b6b;color:white;padding:15px;border-radius:8px;margin:20px 0;">
            ❌ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="background:#51cf66;color:white;padding:15px;border-radius:8px;margin:20px 0;">
            ✅ <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="post" id="purchaseForm">
        <div class="points-packages">
            <div class="package" onclick="selectPackage(100, 2.99)">
                <div class="package-points">100</div>
                <div>Points</div>
                <div class="package-price">2.99€</div>
            </div>
            
            <div class="package" onclick="selectPackage(500, 12.99)">
                <div class="package-points">500</div>
                <div>Points</div>
                <div class="package-price">12.99€</div>
                <div class="bonus">+50 BONUS</div>
            </div>
            
            <div class="package" onclick="selectPackage(1000, 24.99)">
                <div class="package-points">1000</div>
                <div>Points</div>
                <div class="package-price">24.99€</div>
                <div class="bonus">+150 BONUS</div>
            </div>
            
            <div class="package" onclick="selectPackage(2500, 49.99)">
                <div class="package-points">2500</div>
                <div>Points</div>
                <div class="package-price">49.99€</div>
                <div class="bonus">+500 BONUS</div>
            </div>
        </div>

        <div class="card-form" id="cardForm" style="display:none;">
            <h3>Informations de Paiement</h3>
            <p style="color:#888;">💳 Simulation de paiement sécurisé</p>
            
            <input type="hidden" name="amount" id="selectedAmount">
            
            <input type="text" name="card_number" class="card-input" placeholder="1234 5678 9012 3456">
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <input type="text" name="card_expiry" class="card-input" placeholder="MM/YY">
                <input type="text" name="card_cvc" class="card-input" placeholder="123">
            </div>
            
            <button type="submit" style="width:100%;background:#51cf66;color:white;padding:15px;border:none;border-radius:8px;font-size:18px;margin-top:20px;">
                🔒 Finaliser l'Achat
            </button>
        </div>
    </form>

    <div style="text-align:center;margin:30px 0;">
        <a href="<?= $base_url ?>/profile">← Retour au Profil</a>
    </div>
</div>

<script src="assets/js/validation.js"></script>
<script>
function selectPackage(points, price) {
    // Désélectionner tous les packages
    document.querySelectorAll('.package').forEach(p => p.classList.remove('selected'));
    
    // Sélectionner le package cliqué
    event.currentTarget.classList.add('selected');
    
    // Définir la quantité
    document.getElementById('selectedAmount').value = points;
    
    // Afficher le formulaire
    document.getElementById('cardForm').style.display = 'block';
    
    // Scroll vers le formulaire
    document.getElementById('cardForm').scrollIntoView({behavior: 'smooth'});
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('purchaseForm');
    const cardNumberInput = document.querySelector('input[name="card_number"]');
    const expiryInput = document.querySelector('input[name="card_expiry"]');
    const cvcInput = document.querySelector('input[name="card_cvc"]');

    // Ajouter conteneur pour messages
    const cardForm = document.getElementById('cardForm');
    const messagesDiv = document.createElement('div');
    messagesDiv.id = 'purchaseMessages';
    cardForm.insertBefore(messagesDiv, cardForm.querySelector('h3').nextSibling);

    // Validation carte en temps réel avec sécurisation
    cardNumberInput.addEventListener('input', function(e) {
        // Sécuriser l'input
        const sanitized = formValidators.sanitizeInput(e.target.value);
        let value = sanitized.replace(/\\s+/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formattedValue;
        
        let result = formValidators.validateCardNumber(formattedValue);
        // Accepter toutes les cartes mais vérifier la longueur
        if (value.length >= 13 && value.length <= 19) {
            result.valid = true;
            result.message = '';
        } else if (value.length > 0 && value.length < 13) {
            result.valid = false;
            result.message = 'Numéro de carte trop court (minimum 13 chiffres)';
        } else if (value.length > 19) {
            result.valid = false;
            result.message = 'Numéro de carte trop long (maximum 19 chiffres)';
        }
        
        if (!result.valid && value.length > 0) {
            validator.setInputState('card_number', 'error');
            if (value.length >= 13) {
                validator.showToast(result.message, 'warning', 3000);
            }
        } else if (result.valid) {
            validator.setInputState('card_number', 'success');
        } else {
            validator.setInputState('card_number', '');
        }
    });

    // Validation expiry en temps réel avec sécurisation
    expiryInput.addEventListener('input', function(e) {
        const sanitized = formValidators.sanitizeInput(e.target.value);
        let value = sanitized.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.slice(0,2) + '/' + value.slice(2,4);
        }
        e.target.value = value;
        
        if (value.length === 5) {
            const result = formValidators.validateExpiry(value);
            if (!result.valid) {
                validator.setInputState('card_expiry', 'error');
                validator.showToast(result.message, 'warning', 3000);
            } else {
                validator.setInputState('card_expiry', 'success');
            }
        } else {
            validator.setInputState('card_expiry', '');
        }
    });

    // Validation CVC en temps réel avec sécurisation
    cvcInput.addEventListener('input', function(e) {
        const sanitized = formValidators.sanitizeInput(e.target.value);
        const value = sanitized.replace(/\D/g, '').substring(0, 4);
        e.target.value = value;
        
        const result = formValidators.validateCVC(value);
        
        if (!result.valid && value.length > 0) {
            validator.setInputState('card_cvc', 'error');
            if (value.length >= 3) {
                validator.showToast(result.message, 'warning', 3000);
            }
        } else if (result.valid) {
            validator.setInputState('card_cvc', 'success');
        } else {
            validator.setInputState('card_cvc', '');
        }
    });

    // Validation à la soumission
    form.addEventListener('submit', (e) => {
        e.preventDefault(); // Toujours empêcher la soumission par défaut
        
        const amount = parseInt(document.getElementById('selectedAmount').value);
        
        // Vérifier qu'un package est sélectionné
        if (!amount || amount < 100) {
            validator.showToast('Veuillez sélectionner un package de points', 'error');
            return;
        }
        
        // Vérification des champs vides d'abord
        const cardValue = cardNumberInput.value.replace(/\s/g, '').trim();
        const expiryValue = expiryInput.value.trim();
        const cvcValue = cvcInput.value.trim();
        
        if (!cardValue) {
            validator.setInputState('card_number', 'error');
            validator.shakeElement('card_number');
            validator.showToast('Numéro de carte requis', 'error');
            return;
        }
        
        if (!expiryValue) {
            validator.setInputState('card_expiry', 'error');
            validator.shakeElement('card_expiry');
            validator.showToast('Date d\'expiration requise', 'error');
            return;
        }
        
        if (!cvcValue) {
            validator.setInputState('card_cvc', 'error');
            validator.shakeElement('card_cvc');
            validator.showToast('Code CVC requis', 'error');
            return;
        }
        
        // Si pas vides, vérifier la validité
        const cardResult = formValidators.validateCardNumber(cardValue);
        // Override: accepter toutes les cartes avec la bonne longueur
        if (cardValue.length >= 13 && cardValue.length <= 19) {
            cardResult.valid = true;
            cardResult.message = '';
        }
        
        const expiryResult = formValidators.validateExpiry(expiryValue);
        const cvcResult = formValidators.validateCVC(cvcValue);
        
        if (!cardResult.valid) {
            validator.setInputState('card_number', 'error');
            validator.shakeElement('card_number');
            if (cardValue.length < 13) {
                validator.showToast('Numéro de carte trop court (minimum 13 chiffres)', 'error');
            } else if (cardValue.length > 19) {
                validator.showToast('Numéro de carte trop long (maximum 19 chiffres)', 'error');
            } else {
                validator.showToast(cardResult.message, 'error');
            }
            return;
        }
        
        if (!expiryResult.valid) {
            validator.setInputState('card_expiry', 'error');
            validator.shakeElement('card_expiry');
            validator.showToast(expiryResult.message, 'error');
            return;
        }
        
        if (!cvcResult.valid) {
            validator.setInputState('card_cvc', 'error');
            validator.shakeElement('card_cvc');
            validator.showToast(cvcResult.message, 'error');
            return;
        }
        
        // Si tout est valide, soumettre le formulaire
        validator.showToast(`Achat de ${amount} points en cours...`, 'info', 2000);
        setTimeout(() => {
            form.submit();
        }, 500);
    });
});
</script>
</body>
</html>