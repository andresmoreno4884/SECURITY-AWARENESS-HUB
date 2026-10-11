<?php
declare(strict_types=1);

/**
 * Modelo de acceso a datos de la tabla `asignaciones`.
 * Nota: la tabla real incluye las columnas `tipo` y `evaluacion_id`
 * (usadas por los endpoints), aunque el SQL base no las declaraba.
 */
class AsignacionModel
{
    /** Listado con filtros. Réplica de asignaciones.php (GET). */
    public static function listar(string $search = "", string $estado = "", string $tipo = ""): array
    {
        $pdo = Database::connect();

        $sql = "
            SELECT
                a.id, a.usuario_id, a.curso_id, a.evaluacion_id, a.tipo,
                a.fecha_asignacion, a.estado,
                CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                u.correo,
                c.titulo AS curso,
                e.titulo AS evaluacion
            FROM asignaciones a
            INNER JOIN usuarios u ON u.id = a.usuario_id
            LEFT JOIN cursos c ON c.id = a.curso_id
            LEFT JOIN evaluaciones e ON e.id = a.evaluacion_id
            WHERE 1 = 1
        ";

        $params = [];

        if ($search !== "") {
            $sql .= " AND (
                        CONCAT(u.nombres, ' ', u.apellidos) LIKE :searchUsuario
                        OR c.titulo LIKE :searchCurso
                        OR e.titulo LIKE :searchEvaluacion
                     )";
            $value = "%" . $search . "%";
            $params[":searchUsuario"] = $value;
            $params[":searchCurso"] = $value;
            $params[":searchEvaluacion"] = $value;
        }

        if (in_array($estado, ["pendiente", "progreso", "completado"], true)) {
            $sql .= " AND a.estado = :estado";
            $params[":estado"] = $estado;
        }

        if (in_array($tipo, ["curso", "evaluacion"], true)) {
            $sql .= " AND a.tipo = :tipo";
            $params[":tipo"] = $tipo;
        }

        $sql .= " ORDER BY a.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function estadisticas(): array
    {
        $pdo = Database::connect();

        $row = $pdo->query("
            SELECT
                COUNT(*) AS total,
                SUM(estado IN ('pendiente', 'progreso')) AS activas,
                SUM(estado = 'completado') AS completadas
            FROM asignaciones
        ")->fetch(PDO::FETCH_ASSOC);

        return [
            "total"       => (int) ($row["total"] ?? 0),
            "activas"     => (int) ($row["activas"] ?? 0),
            "completadas" => (int) ($row["completadas"] ?? 0),
        ];
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            INSERT INTO asignaciones (usuario_id, curso_id, evaluacion_id, tipo, fecha_asignacion, estado)
            VALUES (:usuario_id, :curso_id, :evaluacion_id, :tipo, :fecha, :estado)
        ");
        $stmt->execute([
            ":usuario_id"    => $data["usuario_id"],
            ":curso_id"      => $data["curso_id"],
            ":evaluacion_id" => ($data["evaluacion_id"] > 0 ? $data["evaluacion_id"] : null),
            ":tipo"          => $data["tipo"],
            ":fecha"         => $data["fecha"],
            ":estado"        => $data["estado"],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            UPDATE asignaciones
            SET usuario_id = :usuario_id, curso_id = :curso_id, evaluacion_id = :evaluacion_id,
                tipo = :tipo, fecha_asignacion = :fecha, estado = :estado
            WHERE id = :id
        ");

        return $stmt->execute([
            ":id"            => $id,
            ":usuario_id"    => $data["usuario_id"],
            ":curso_id"      => $data["curso_id"],
            ":evaluacion_id" => ($data["evaluacion_id"] > 0 ? $data["evaluacion_id"] : null),
            ":tipo"          => $data["tipo"],
            ":fecha"         => $data["fecha"],
            ":estado"        => $data["estado"],
        ]);
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("UPDATE asignaciones SET estado = :estado WHERE id = :id");

        return $stmt->execute([
            ":estado" => $estado,
            ":id"     => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("DELETE FROM asignaciones WHERE id = :id");

        return $stmt->execute([":id" => $id]);
    }

    /** Cursos asignados a un usuario (tipo 'curso'). Réplica de mis_cursos.php. */
    public static function cursosPorUsuario(int $usuarioId): array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            SELECT
                a.id AS asignacion_id,
                a.estado AS estado_asignacion,
                a.fecha_asignacion,
                c.id, c.titulo, c.descripcion, c.nivel_dificultad,
                c.duracion, c.numero_modulos, c.porcentaje_aprobacion, c.estado
            FROM asignaciones a
            INNER JOIN cursos c ON c.id = a.curso_id
            WHERE a.usuario_id = :usuario_id
              AND a.tipo = 'curso'
            ORDER BY a.id DESC
        ");
        $stmt->execute([":usuario_id" => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Marca como completadas las asignaciones de curso de un usuario. */
    public static function completarCurso(int $usuarioId, int $cursoId): void
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            UPDATE asignaciones
            SET estado = 'completado'
            WHERE usuario_id = :usuario_id AND curso_id = :curso_id AND tipo = 'curso'
        ");
        $stmt->execute([
            ":usuario_id" => $usuarioId,
            ":curso_id"   => $cursoId,
        ]);
    }
}
