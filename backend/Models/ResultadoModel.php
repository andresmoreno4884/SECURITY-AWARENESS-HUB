<?php
declare(strict_types=1);

/**
 * Modelo de la tabla `resultados` (calificaciones de evaluaciones presentadas
 * por los usuarios). Es la tabla que usan mis_evaluaciones.php, resultados.php
 * y reportes.php.
 */
class ResultadoModel
{
    /** Último resultado de un usuario (para la vista de curso). */
    public static function ultimoPorUsuario(int $usuarioId): ?array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            SELECT porcentaje, preguntas_totales, preguntas_correctas,
                   preguntas_incorrectas, aprobado, fecha_resultado
            FROM resultados
            WHERE usuario_id = :usuario_id
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([":usuario_id" => $usuarioId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Listado administrativo con filtros. Réplica de resultados.php (GET). */
    public static function listar(string $search = "", int $cursoId = 0, int $evaluacionId = 0, string $aprobado = ""): array
    {
        $pdo = Database::connect();

        $sql = "
            SELECT
                r.id, r.usuario_id, r.evaluacion_id, r.puntuacion, r.porcentaje,
                r.preguntas_totales, r.preguntas_correctas, r.preguntas_incorrectas,
                r.aprobado, r.fecha_resultado,
                CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                u.correo,
                e.titulo AS evaluacion,
                c.id AS curso_id,
                c.titulo AS curso
            FROM resultados r
            INNER JOIN usuarios u ON u.id = r.usuario_id
            INNER JOIN evaluaciones e ON e.id = r.evaluacion_id
            LEFT JOIN cursos c ON c.id = e.curso_id
            WHERE 1 = 1
        ";

        $params = [];

        if ($search !== "") {
            $sql .= " AND (
                        CONCAT(u.nombres, ' ', u.apellidos) LIKE :searchUsuario
                        OR u.correo LIKE :searchCorreo
                     )";
            $value = "%{$search}%";
            $params[":searchUsuario"] = $value;
            $params[":searchCorreo"] = $value;
        }

        if ($cursoId > 0) {
            $sql .= " AND c.id = :cursoId";
            $params[":cursoId"] = $cursoId;
        }

        if ($evaluacionId > 0) {
            $sql .= " AND e.id = :evaluacionId";
            $params[":evaluacionId"] = $evaluacionId;
        }

        if (in_array($aprobado, ["si", "no"], true)) {
            $sql .= " AND r.aprobado = :aprobado";
            $params[":aprobado"] = $aprobado;
        }

        $sql .= " ORDER BY r.fecha_resultado DESC, r.id DESC";

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
                COALESCE(AVG(porcentaje), 0) AS promedio,
                SUM(aprobado = 'si') AS aprobados
            FROM resultados
        ")->fetch(PDO::FETCH_ASSOC);

        return [
            "total"     => (int) ($row["total"] ?? 0),
            "promedio"  => round((float) ($row["promedio"] ?? 0), 2),
            "aprobados" => (int) ($row["aprobados"] ?? 0),
        ];
    }

    /** Guarda el resultado de una evaluación presentada. */
    public static function guardar(int $usuarioId, int $evaluacionId, array $data): void
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            INSERT INTO resultados
                (usuario_id, evaluacion_id, puntuacion, porcentaje,
                 preguntas_totales, preguntas_correctas, preguntas_incorrectas, aprobado)
            VALUES
                (:usuario_id, :evaluacion_id, :puntuacion, :porcentaje,
                 :totales, :correctas, :incorrectas, :aprobado)
        ");
        $stmt->execute([
            ":usuario_id"   => $usuarioId,
            ":evaluacion_id" => $evaluacionId,
            ":puntuacion"   => $data["porcentaje"],
            ":porcentaje"   => $data["porcentaje"],
            ":totales"      => $data["preguntas_totales"],
            ":correctas"    => $data["preguntas_correctas"],
            ":incorrectas"  => $data["preguntas_incorrectas"],
            ":aprobado"     => $data["aprobado"],
        ]);
    }
}
