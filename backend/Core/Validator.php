<?php
declare(strict_types=1);

/**
 * Utilidades de validación y hash de contraseñas.
 */
class Validator
{
    /**
     * Reglas: mínimo 8 caracteres, al menos 1 número y 1 carácter especial.
     * Devuelve [bool valido, string mensaje].
     */
    public static function validatePassword(string $password): array
    {
        if (strlen($password) < 8) {
            return [false, "La contraseña debe tener al menos 8 caracteres."];
        }

        if (preg_match("/[0-9]/", $password) < 1) {
            return [false, "La contraseña debe tener al menos 1 número."];
        }

        if (!preg_match("/[^A-Za-z0-9]/", $password)) {
            return [false, "La contraseña debe tener al menos un carácter especial."];
        }

        return [true, ""];
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function isEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
