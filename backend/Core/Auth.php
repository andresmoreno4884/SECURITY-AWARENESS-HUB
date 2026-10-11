<?php
declare(strict_types=1);

/**
 * Gestión de sesión y autorización.
 * Mantiene exactamente las mismas claves de sesión que usan los endpoints
 * y el frontend actual: user_id, nombres, apellidos, correo, rol, estado,
 * authenticated.
 */
class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(array $user): void
    {
        self::start();
        session_regenerate_id(true);

        $_SESSION["user_id"]       = $user["id"];
        $_SESSION["nombres"]       = $user["nombres"];
        $_SESSION["apellidos"]     = $user["apellidos"];
        $_SESSION["correo"]        = $user["correo"];
        $_SESSION["rol"]           = $user["rol"];
        $_SESSION["estado"]        = $user["estado"];
        $_SESSION["authenticated"] = true;
    }

    public static function logout(): void
    {
        self::start();

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                "",
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function check(): bool
    {
        return isset($_SESSION["authenticated"]) && $_SESSION["authenticated"] === true;
    }

    public static function isAdmin(): bool
    {
        return self::check() && (($_SESSION["rol"] ?? "") === "admin");
    }

    public static function userId(): ?int
    {
        return isset($_SESSION["user_id"]) ? (int) $_SESSION["user_id"] : null;
    }

    public static function rol(): string
    {
        return (string) ($_SESSION["rol"] ?? "");
    }

    /** Datos públicos del usuario en sesión (para respuestas de login). */
    public static function user(): array
    {
        return [
            "id"        => $_SESSION["user_id"] ?? null,
            "nombres"   => $_SESSION["nombres"] ?? "",
            "apellidos" => $_SESSION["apellidos"] ?? "",
            "correo"    => $_SESSION["correo"] ?? "",
            "rol"       => $_SESSION["rol"] ?? "",
            "estado"    => $_SESSION["estado"] ?? "",
        ];
    }

    /** Corta la petición con 401 si no hay sesión activa. */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            Response::json(false, "No hay una sesión activa.", [], 401);
        }
    }

    /** Corta la petición con 401/403 si no es administrador. */
    public static function requireAdmin(): void
    {
        self::requireLogin();

        if (!self::isAdmin()) {
            Response::json(false, "No tienes permisos para realizar esta acción.", [], 403);
        }
    }
}
