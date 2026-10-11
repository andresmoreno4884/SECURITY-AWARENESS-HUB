<?php
declare(strict_types=1);

/**
 * Abstracción de la petición HTTP entrante.
 */
class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER["REQUEST_METHOD"] ?? "GET");
    }

    public static function isGet(): bool
    {
        return self::method() === "GET";
    }

    public static function isPost(): bool
    {
        return self::method() === "POST";
    }

    /** Cuerpo decodificado como array (JSON). Devuelve [] si no hay body válido. */
    public static function body(): array
    {
        $raw = file_get_contents("php://input");
        if ($raw === false || trim($raw) === "") {
            return [];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /** Valor del cuerpo JSON con opción de default. */
    public static function input(string $key, $default = null)
    {
        $body = self::body();
        return $body[$key] ?? $default;
    }

    /** Valor de la query string con opción de default. */
    public static function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /** Texto de query string recortado. */
    public static function queryTrim(string $key): string
    {
        $value = $_GET[$key] ?? "";
        return is_string($value) ? trim($value) : "";
    }

    /** Entero de query string. */
    public static function queryInt(string $key): int
    {
        return (int) ($_GET[$key] ?? 0);
    }
}
