// Validation JavaScript réactive avec animations
class ValidationManager {
    constructor() {
        this.warnings = new Map();
        this.initStyles();
    }

    initStyles() {
        // Ajouter les styles CSS pour les animations
        const style = document.createElement('style');
        style.textContent = `
            .warning-message {
                background: linear-gradient(135deg, #ff6b6b, #ee5a52);
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                margin: 10px 0;
                font-size: 14px;
                font-weight: 500;
                box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
                transform: scale(0);
                opacity: 0;
                transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                transform-origin: center;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .warning-message.show {
                transform: scale(1);
                opacity: 1;
            }

            .warning-message.pulse {
                animation: pulseWarning 0.6s ease-in-out;
            }

            .success-message {
                background: linear-gradient(135deg, #51cf66, #40c057);
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                margin: 10px 0;
                font-size: 14px;
                font-weight: 500;
                box-shadow: 0 4px 15px rgba(81, 207, 102, 0.3);
                transform: scale(0);
                opacity: 0;
                transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                transform-origin: center;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .success-message.show {
                transform: scale(1);
                opacity: 1;
            }

            .input-error {
                border: 2px solid #ff6b6b !important;
                background: rgba(255, 107, 107, 0.1) !important;
                box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
                transition: all 0.3s ease;
            }

            .input-success {
                border: 2px solid #51cf66 !important;
                background: rgba(81, 207, 102, 0.1) !important;
                box-shadow: 0 0 0 3px rgba(81, 207, 102, 0.1);
                transition: all 0.3s ease;
            }

            .input-warning {
                border: 2px solid #ffd43b !important;
                background: rgba(255, 212, 59, 0.1) !important;
                box-shadow: 0 0 0 3px rgba(255, 212, 59, 0.1);
                transition: all 0.3s ease;
            }

            @keyframes pulseWarning {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); box-shadow: 0 6px 25px rgba(255, 107, 107, 0.4); }
                100% { transform: scale(1); }
            }

            .shake {
                animation: shake 0.5s ease-in-out;
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }

            .strength-meter {
                height: 4px;
                background: #2c2c2c;
                border-radius: 2px;
                margin-top: 5px;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .strength-fill {
                height: 100%;
                transition: all 0.3s ease;
                border-radius: 2px;
            }

            .strength-weak { background: linear-gradient(90deg, #ff6b6b, #fa5252); width: 33%; }
            .strength-medium { background: linear-gradient(90deg, #ffd43b, #fab005); width: 66%; }
            .strength-strong { background: linear-gradient(90deg, #51cf66, #40c057); width: 100%; }
            
            /* Toast flottant avec overlay */
            .toast-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.85);
                backdrop-filter: blur(10px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 999999;
                opacity: 0;
                transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                pointer-events: all;
                padding: 20px;
                box-sizing: border-box;
            }
            
            .toast-overlay.show {
                opacity: 1;
            }
            
            .toast {
                background: linear-gradient(145deg, #1e1e1e, #2a2a2a);
                border-radius: 24px;
                padding: 32px 36px;
                max-width: 520px;
                width: 100%;
                min-width: 320px;
                box-shadow: 
                    0 32px 64px rgba(0, 0, 0, 0.8),
                    0 16px 32px rgba(0, 0, 0, 0.6),
                    0 0 0 1px rgba(255, 255, 255, 0.1) inset,
                    0 2px 0 rgba(255, 255, 255, 0.05) inset;
                display: flex;
                align-items: flex-start;
                gap: 20px;
                color: #ffffff;
                font-size: 16px;
                font-weight: 500;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                transform: scale(0.8) translateY(60px);
                transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
                border: 2px solid transparent;
                position: relative;
                line-height: 1.5;
            }
            
            .toast-overlay.show .toast {
                transform: scale(1) translateY(0);
            }
            
            .toast-error {
                border-color: #ff3b30;
                background: linear-gradient(145deg, #2d1a1a, #3d2020);
                box-shadow: 
                    0 32px 64px rgba(255, 59, 48, 0.4),
                    0 16px 32px rgba(255, 59, 48, 0.2),
                    0 0 0 1px rgba(255, 59, 48, 0.3) inset;
            }
            
            .toast-warning {
                border-color: #ff9500;
                background: linear-gradient(145deg, #2d2419, #3d3020);
                box-shadow: 
                    0 32px 64px rgba(255, 149, 0, 0.4),
                    0 16px 32px rgba(255, 149, 0, 0.2),
                    0 0 0 1px rgba(255, 149, 0, 0.3) inset;
            }
            
            .toast-success {
                border-color: #34c759;
                background: linear-gradient(145deg, #1a2d1a, #203d20);
                box-shadow: 
                    0 32px 64px rgba(52, 199, 89, 0.4),
                    0 16px 32px rgba(52, 199, 89, 0.2),
                    0 0 0 1px rgba(52, 199, 89, 0.3) inset;
            }
            
            .toast-info {
                border-color: #007aff;
                background: linear-gradient(145deg, #1a1f2d, #20243d);
                box-shadow: 
                    0 32px 64px rgba(0, 122, 255, 0.4),
                    0 16px 32px rgba(0, 122, 255, 0.2),
                    0 0 0 1px rgba(0, 122, 255, 0.3) inset;
            }
            
            .toast-icon {
                font-size: 32px;
                flex-shrink: 0;
                line-height: 1;
                margin-top: 2px;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
            }
            
            .toast-message {
                flex: 1;
                font-weight: 600;
                line-height: 1.4;
                padding-right: 24px;
                word-break: break-word;
                letter-spacing: -0.01em;
            }
            
            .toast-close {
                position: absolute;
                top: 12px;
                right: 12px;
                background: rgba(255, 255, 255, 0.15);
                border: none;
                color: #ffffff;
                font-size: 20px;
                cursor: pointer;
                padding: 8px;
                line-height: 1;
                transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                width: 32px;
                height: 32px;
                margin: 0;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                backdrop-filter: blur(4px);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            }
            
            .toast-close:hover {
                background: rgba(255, 255, 255, 0.25);
                transform: scale(1.1);
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            }
            
            .toast-close:active {
                transform: scale(0.95);
            }
            
            /* Animation d'apparition améliorée */
            @keyframes toastSlideIn {
                0% {
                    opacity: 0;
                    transform: scale(0.7) translateY(80px);
                }
                60% {
                    transform: scale(1.05) translateY(-10px);
                }
                100% {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }
            
            .toast-overlay.show .toast {
                animation: toastSlideIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            }
            
            /* Responsive design */
            @media (max-width: 640px) {
                .toast {
                    margin: 0 16px;
                    padding: 24px 20px;
                    border-radius: 20px;
                    font-size: 15px;
                }
                
                .toast-icon {
                    font-size: 28px;
                }
                
                .toast-close {
                    top: 8px;
                    right: 8px;
                    width: 28px;
                    height: 28px;
                    font-size: 18px;
                }
            }
        `;
        document.head.appendChild(style);
    }

    showMessage(containerId, message, type = 'warning', duration = 4000) {
        this.showToast(message, type, duration);
    }
    
    showToast(message, type = 'warning', duration = 5000) {
        console.log(`🎯 Affichage toast: ${message} (${type})`);
        
        // Supprimer le toast existant s'il y en a un
        const existingToast = document.querySelector('.toast-overlay');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Créer l'overlay
        const overlay = document.createElement('div');
        overlay.className = 'toast-overlay';
        
        // Créer le toast
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        // Icône selon le type
        const icons = {
            error: '⚠️',
            warning: '⚡',
            success: '✅',
            info: '💡'
        };
        
        toast.innerHTML = `
            <div class="toast-icon">${icons[type] || '⚠️'}</div>
            <div class="toast-message">${this.escapeHtml(message)}</div>
            <button class="toast-close">✕</button>
        `;
        
        overlay.appendChild(toast);
        document.body.appendChild(overlay);
        
        console.log('✅ Toast ajouté au DOM');
        
        // Animation d'entrée
        requestAnimationFrame(() => {
            overlay.classList.add('show');
        });
        
        // Gérer la fermeture
        const closeToast = () => {
            if (overlay.parentNode) {
                overlay.classList.remove('show');
                setTimeout(() => {
                    if (overlay.parentNode) {
                        overlay.remove();
                    }
                }, 300);
            }
        };
        
        // Auto-fermeture
        if (duration > 0) {
            setTimeout(closeToast, duration);
        }
        
        // Fermer avec le bouton
        toast.querySelector('.toast-close').addEventListener('click', closeToast);
        
        // Fermer en cliquant sur l'overlay
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeToast();
            }
        });
        
        // Fermer avec Escape
        const escapeHandler = (e) => {
            if (e.key === 'Escape') {
                closeToast();
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
        
        console.log('🎯 Toast configuré avec événements');
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    clearMessage(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const existing = container.querySelector('.warning-message, .success-message');
        if (existing) {
            existing.style.transform = 'scale(0)';
            existing.style.opacity = '0';
            setTimeout(() => {
                if (existing.parentNode) {
                    existing.parentNode.removeChild(existing);
                }
            }, 300);
        }
        this.warnings.delete(containerId);
    }

    setInputState(inputId, state) {
        const input = document.getElementById(inputId) || document.querySelector(`input[name="${inputId}"]`);
        if (!input) return;

        // Supprimer les anciennes classes
        input.classList.remove('input-error', 'input-success', 'input-warning');
        
        // Ajouter la nouvelle classe
        if (state) {
            input.classList.add(`input-${state}`);
        }
    }

    shakeElement(elementId) {
        const element = document.getElementById(elementId) || document.querySelector(`input[name="${elementId}"]`);
        if (!element) return;

        element.classList.add('shake');
        setTimeout(() => {
            element.classList.remove('shake');
        }, 500);
    }

    showPasswordStrength(inputId, strengthId) {
        const input = document.getElementById(inputId) || document.querySelector(`input[name="${inputId}"]`);
        const container = document.getElementById(strengthId);
        
        if (!input || !container) return;

        input.addEventListener('input', (e) => {
            const password = e.target.value;
            const strength = this.calculatePasswordStrength(password);
            
            let strengthEl = container.querySelector('.strength-meter');
            if (!strengthEl) {
                strengthEl = document.createElement('div');
                strengthEl.className = 'strength-meter';
                strengthEl.innerHTML = '<div class="strength-fill"></div>';
                container.appendChild(strengthEl);
            }

            const fill = strengthEl.querySelector('.strength-fill');
            fill.className = 'strength-fill';
            
            if (password.length > 0) {
                if (strength.score < 2) {
                    fill.classList.add('strength-weak');
                } else if (strength.score < 4) {
                    fill.classList.add('strength-medium');
                } else {
                    fill.classList.add('strength-strong');
                }
            } else {
                fill.style.width = '0%';
            }
        });
    }

    calculatePasswordStrength(password) {
        let score = 0;
        const checks = {
            length: password.length >= 6,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            numbers: /\d/.test(password),
            symbols: /[^A-Za-z0-9]/.test(password)
        };

        Object.values(checks).forEach(check => {
            if (check) score++;
        });

        return { score, checks };
    }
}

// Validateurs spécifiques
class FormValidators {
    constructor(validator) {
        this.validator = validator;
    }

    validateUsername(username) {
        if (!username || username.length < 3) {
            return { valid: false, message: "Le nom d'utilisateur doit contenir au moins 3 caractères" };
        }
        if (!/^[a-zA-Z0-9_-]+$/.test(username)) {
            return { valid: false, message: "Le nom d'utilisateur ne peut contenir que des lettres, chiffres, _ et -" };
        }
        if (username.length > 20) {
            return { valid: false, message: "Le nom d'utilisateur ne peut pas dépasser 20 caractères" };
        }
        return { valid: true };
    }

    validatePassword(password) {
        if (!password || password.length < 6) {
            return { valid: false, message: "Le mot de passe doit contenir au moins 6 caractères" };
        }
        if (password.length > 128) {
            return { valid: false, message: "Le mot de passe ne peut pas dépasser 128 caractères" };
        }
        return { valid: true };
    }

    validatePasswordMatch(password1, password2) {
        if (password1 !== password2) {
            return { valid: false, message: "Les mots de passe ne correspondent pas" };
        }
        return { valid: true };
    }

    validateAmount(amount, min = 10, max = 10000) {
        const num = parseInt(amount);
        if (isNaN(num) || num < min) {
            return { valid: false, message: `Le montant minimum est de ${min} points` };
        }
        if (num > max) {
            return { valid: false, message: `Le montant maximum est de ${max} points` };
        }
        return { valid: true };
    }

    validateCardNumber(cardNumber) {
        const cleaned = cardNumber.replace(/\s/g, '');
        if (!/^\d{13,19}$/.test(cleaned)) {
            return { valid: false, message: "Numéro de carte invalide (13-19 chiffres requis)" };
        }
        
        // Algorithme de Luhn simplifié pour la démo
        let sum = 0;
        let alternate = false;
        for (let i = cleaned.length - 1; i >= 0; i--) {
            let n = parseInt(cleaned.charAt(i));
            if (alternate) {
                n *= 2;
                if (n > 9) n = (n % 10) + 1;
            }
            sum += n;
            alternate = !alternate;
        }
        
        if (sum % 10 !== 0) {
            return { valid: false, message: "Numéro de carte invalide (échec validation Luhn)" };
        }
        
        return { valid: true };
    }

    validateExpiry(expiry) {
        if (!/^\d{2}\/\d{2}$/.test(expiry)) {
            return { valid: false, message: "Format requis: MM/YY" };
        }
        
        const [month, year] = expiry.split('/').map(n => parseInt(n));
        const now = new Date();
        const currentYear = now.getFullYear() % 100;
        const currentMonth = now.getMonth() + 1;
        
        if (month < 1 || month > 12) {
            return { valid: false, message: "Mois invalide (01-12)" };
        }
        
        if (year < currentYear || (year === currentYear && month < currentMonth)) {
            return { valid: false, message: "Carte expirée" };
        }
        
        return { valid: true };
    }

    validateCVC(cvc) {
        if (!/^\d{3,4}$/.test(cvc)) {
            return { valid: false, message: "CVC invalide (3-4 chiffres requis)" };
        }
        return { valid: true };
    }

    validateEmpty(value, fieldName = 'Ce champ') {
        const isEmpty = !value || value.trim() === '';
        return {
            valid: !isEmpty,
            message: isEmpty ? `${fieldName} est requis` : ''
        };
    }

    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const isEmpty = !email || email.trim() === '';
        
        if (isEmpty) {
            return { valid: false, message: 'Email requis' };
        }
        
        if (!emailRegex.test(email)) {
            return { valid: false, message: 'Format email invalide' };
        }
        
        // Domaines interdits
        const blockedDomains = ['tempmail.org', '10minutemail.com', 'guerrillamail.com'];
        const domain = email.split('@')[1]?.toLowerCase();
        
        if (blockedDomains.includes(domain)) {
            return { valid: false, message: 'Domaine email non autorisé' };
        }
        
        return { valid: true, message: '' };
    }
    
    // Sécurisation contre les injections
    sanitizeInput(value) {
        if (typeof value !== 'string') return '';
        
        // Supprimer les caractères dangereux
        return value
            .replace(/[<>"'&]/g, '') // XSS basique
            .replace(/[\x00-\x1f\x7f-\x9f]/g, '') // Caractères de contrôle
            .replace(/script|javascript|vbscript|onload|onerror|onclick/gi, '') // Scripts
            .replace(/union|select|insert|update|delete|drop|exec|script/gi, '') // SQL
            .trim()
            .substring(0, 1000); // Limite de longueur
    }
    
    validateAndSanitize(value, fieldName = 'Ce champ') {
        const sanitized = this.sanitizeInput(value);
        const emptyResult = this.validateEmpty(sanitized, fieldName);
        
        return {
            valid: emptyResult.valid,
            message: emptyResult.message,
            sanitized: sanitized
        };
    }
}

// Instance globale
window.ValidationManager = ValidationManager;
window.FormValidators = FormValidators;

// Auto-initialisation
document.addEventListener('DOMContentLoaded', () => {
    console.log('🎯 Initialisation du système de validation...');
    
    // Créer les instances globales
    window.validator = new ValidationManager();
    window.formValidators = new FormValidators(window.validator);
    
    console.log('✅ Validator initialisé:', window.validator);
    console.log('✅ FormValidators initialisé:', window.formValidators);
    
    // Test de base
    setTimeout(() => {
        if (window.validator && typeof window.validator.showToast === 'function') {
            console.log('🟢 Système de toast opérationnel');
        } else {
            console.error('🔴 Problème avec le système de toast');
        }
    }, 100);
});