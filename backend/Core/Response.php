<?php
declare(strict_types=1);

/**
 * Respuesta JSON centralizada.
 * Contrato idéntico al de los endpoints actuales:
 * { "success": bool, "message": string, ...$data }
 */
class Response
{
    public static function json(bool $success, string $message = "", array $data = [], int $status = 200): void
    {
        if (!headers_sent()) {
            http_response_code($status);
        }

        echo json_encode(
            array_merge([
                "success" => $success,
                "message" => $message,
            ], $data),
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }
}
