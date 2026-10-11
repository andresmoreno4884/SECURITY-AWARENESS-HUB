<?php
declare(strict_types=1);

/**
 * Modelo de la tabla `actividad_acceso` (registro de login/logout).
 */
class ActividadAccesoModel
{
    /** Registra un acceso (login/logout) con IP opcional. */
    public static function create(array $data): int
    {
        $pdo = Database::connect();

        $sql = "INSERT INTO actividad_acceso (usuario_id, tipo, ip)
                VALUES (:usuario_id, :tipo, :ip)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":usuario_id" => $data["usuario_id"],
            ":tipo"       => $data["tipo"],
            ":ip"         => $data["ip"] ?? null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /** Historial de accesos de un usuario. */
    public static function porUsuario(int $usuarioId): array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            SELECT ac.*, u.nombres AS usuarioNombres
            FROM actividad_acceso ac
            INNER JOIN usuarios u ON u.id = ac.usuario_id
            WHERE ac.usuario_id = :usuario_id
            ORDER BY ac.id DESC
        ");
        $stmt->execute([":usuario_id" => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function all(): array
    {
        $pdo = Database::connect();

        return $pdo->query("
            SELECT ac.*, u.nombres AS usuarioNombres, u.apellidos AS usuarioApellidos, u.correo AS usuarioCorreo
            FROM actividad_acceso ac
            INNER JOIN usuarios u ON u.id = ac.usuario_id
            ORDER BY ac.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function contar(): int
    {
        $pdo = Database::connect();

        return (int) $pdo->query("SELECT COUNT(*) FROM actividad_acceso")->fetchColumn();
    }

    public static function porTipo(string $tipo): array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            SELECT ac.*, u.nombres AS usuarioNombres
            FROM actividad_acceso ac
            INNER JOIN usuarios u ON u.id = ac.usuario_id
            WHERE ac.tipo = :tipo
            ORDER BY ac.id DESC
        ");
        $stmt->execute([":tipo" => $tipo]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
