<?php
declare(strict_types=1);

/**
 * Modelo de la tabla `progreso` (avance de un usuario en una asignación).
 */
class ProgresoModel
{
    /** Todo el progreso con datos de la asignación. */
    public static function all(): array
    {
        $pdo = Database::connect();

        return $pdo->query("
            SELECT p.*, u.nombres AS usuarioNombres, c.titulo AS cursoNombre, a.id AS asignacion_id
            FROM progreso p
            INNER JOIN asignaciones a ON a.id = p.asignacion_id
            INNER JOIN usuarios u ON u.id = a.usuario_id
            INNER JOIN cursos c ON c.id = a.curso_id
            ORDER BY p.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM progreso WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect();

        $sql = "INSERT INTO progreso (asignacion_id, porcentaje, modulos_completados, ultima_actividad)
                VALUES (:asignacion_id, :porcentaje, :modulos_completados, :ultima_actividad)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":asignacion_id"      => $data["asignacion_id"],
            ":porcentaje"         => $data["porcentaje"] ?? 0,
            ":modulos_completados" => $data["modulos_completados"] ?? 0,
            ":ultima_actividad"   => $data["ultima_actividad"] ?? null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connect();

        $sql = "UPDATE progreso
                SET porcentaje = :porcentaje,
                    modulos_completados = :modulos_completados,
                    ultima_actividad = :ultima_actividad
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ":id"                  => $id,
            ":porcentaje"          => $data["porcentaje"] ?? 0,
            ":modulos_completados" => $data["modulos_completados"] ?? 0,
            ":ultima_actividad"    => $data["ultima_actividad"] ?? null,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("DELETE FROM progreso WHERE id = :id");

        return $stmt->execute([":id" => $id]);
    }

    public static function porAsignacion(int $asignacionId): ?array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM progreso WHERE asignacion_id = :asignacion_id LIMIT 1");
        $stmt->execute([":asignacion_id" => $asignacionId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
