// Fonctions utilitaires pour la validation
export const validators = {
    // Valider le format d'un mot de passe sécurisé
    validatePassword(password) {
        const minLength = 10;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
        
        const isValid = password.length >= minLength && hasUpperCase && hasLowerCase && hasNumber && hasSpecialChar;
        
        return {
            isValid,
            errors: [
                password.length < minLength ? `Minimum 10 caractères (actuellement: ${password.length})` : null,
                !hasUpperCase ? 'Au moins une majuscule (A-Z)' : null,
                !hasLowerCase ? 'Au moins une minuscule (a-z)' : null,
                !hasNumber ? 'Au moins un chiffre (0-9)' : null,
                !hasSpecialChar ? 'Au moins un caractère spécial (!@#$%^&*...)' : null
            ].filter(error => error !== null)
        };
    },

    // Afficher les critères du mot de passe
    getPasswordRequirements() {
        return `
✓ Minimum 10 caractères
✓ Au moins une majuscule (A-Z)
✓ Au moins une minuscule (a-z)
✓ Au moins un chiffre (0-9)
✓ Au moins un caractère spécial (!@#$%^&*()_+-=[]{};\\':"\\|,.<>/?)`
    }
};
