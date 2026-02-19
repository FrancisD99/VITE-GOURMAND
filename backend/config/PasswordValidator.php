<?php

class PasswordValidator
{
    // Validé qu'un mot de passe respecte les critères de sécurité
    // Minimum 10 caractères, avec majuscule, minuscule, chiffre et caractère spécial
    public static function validate($password)
    {
        $minLength = 10;
        $hasUpperCase = preg_match('/[A-Z]/', $password);
        $hasLowerCase = preg_match('/[a-z]/', $password);
        $hasNumber = preg_match('/[0-9]/', $password);
        $hasSpecialChar = preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password);

        $isValid = (
            strlen($password) >= $minLength &&
            $hasUpperCase &&
            $hasLowerCase &&
            $hasNumber &&
            $hasSpecialChar
        );

        $errors = [];
        if (strlen($password) < $minLength) {
            $errors[] = "Minimum 10 caractères (actuellement: " . strlen($password) . ")";
        }
        if (!$hasUpperCase) {
            $errors[] = "Au moins une majuscule (A-Z)";
        }
        if (!$hasLowerCase) {
            $errors[] = "Au moins une minuscule (a-z)";
        }
        if (!$hasNumber) {
            $errors[] = "Au moins un chiffre (0-9)";
        }
        if (!$hasSpecialChar) {
            $errors[] = "Au moins un caractère spécial (!@#$%^&*...)";
        }

        return [
            'isValid' => $isValid,
            'errors' => $errors
        ];
    }

    // Retourner les critères requis
    public static function getRequirements()
    {
        return [
            "Minimum 10 caractères",
            "Au moins une majuscule (A-Z)",
            "Au moins une minuscule (a-z)",
            "Au moins un chiffre (0-9)",
            "Au moins un caractère spécial (!@#$%^&*()_+-=[]{};\\':\\\"\\|,.<>/?)"
        ];
    }
}
