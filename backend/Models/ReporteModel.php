<?php
declare(strict_types=1);

/**
 * Modelo para la generación de reportes administrativos.
 * Réplica de reportes.php.
 */
class ReporteModel
{
    /**
     * Genera un reporte por tipo y periodo.
     * Devuelve [filas, columnas, estadisticas].
     */
    public static function generar(string $tipo, string $periodo): array
    {
        $pdo = Database::connect();

        $fechaFiltro = self::filtroPeriodo($periodo);

        switch ($tipo) {
            case "usuarios":
                $sql = "SELECT id, CONCAT(nombres, ' ', apellidos) AS usuario, correo, rol, estado, fecha_registro
                        FROM usuarios WHERE 1 = 1" . $fechaFiltro . " ORDER BY fecha_registro DESC";
                $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                $columns = ["usuario" => "Usuario", "correo" => "Correo", "rol" => "Rol", "estado" => "Estado", "fecha_registro" => "Registro"];
                break;

            case "cursos":
                $sql = "SELECT id, titulo, nivel_dificultad, estado, fecha_creacion
                        FROM cursos WHERE 1 = 1" . str_replace("fecha_registro", "fecha_creacion", $fechaFiltro) . " ORDER BY fecha_creacion DESC";
                $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                $columns = ["titulo" => "Curso", "nivel_dificultad" => "Nivel", "estado" => "Estado", "fecha_creacion" => "Creación"];
                break;

            case "evaluaciones":
                $sql = "SELECT e.id, e.titulo AS evaluacion, c.titulo AS curso, e.estado, e.fecha_creacion
                        FROM evaluaciones e
                        LEFT JOIN cursos c ON c.id = e.curso_id
                        WHERE 1 = 1" . str_replace("fecha_registro", "e.fecha_creacion", $fechaFiltro) . " ORDER BY e.fecha_creacion DESC";
                $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                $columns = ["evaluacion" => "Evaluación", "curso" => "Curso", "estado" => "Estado", "fecha_creacion" => "Creación"];
                break;

            default: // resultados / capacitacion
                $sql = "
                    SELECT
                        CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                        c.titulo AS curso,
                        e.titulo AS evaluacion,
                        r.porcentaje,
                        r.aprobado,
                        r.fecha_resultado
                    FROM resultados r
                    INNER JOIN usuarios u ON u.id = r.usuario_id
                    INNER JOIN evaluaciones e ON e.id = r.evaluacion_id
                    LEFT JOIN cursos c ON c.id = e.curso_id
                    WHERE 1 = 1
                ";
                if ($fechaFiltro !== "") {
                    $sql .= str_replace("fecha_registro", "r.fecha_resultado", $fechaFiltro);
                }
                $sql .= " ORDER BY r.fecha_resultado DESC";
                $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                $columns = ["usuario" => "Usuario", "curso" => "Curso", "evaluacion" => "Evaluación", "porcentaje" => "Porcentaje", "aprobado" => "Resultado", "fecha_resultado" => "Fecha"];
                break;
        }

        $stats = self::estadisticasGenerales();

        return [$rows, $columns, $stats];
    }

    /** Estadísticas globales mostradas en la vista de reportes. */
    public static function estadisticasGenerales(): array
    {
        $pdo = Database::connect();

        $row = $pdo->query("
            SELECT
                (SELECT COUNT(*) FROM usuarios WHERE rol = 'usuario') AS usuarios,
                (SELECT COUNT(*) FROM cursos) AS cursos,
                COALESCE((SELECT AVG(porcentaje) FROM resultados), 0) AS promedio
        ")->fetch(PDO::FETCH_ASSOC);

        return [
            "usuarios" => (int) ($row["usuarios"] ?? 0),
            "cursos"   => (int) ($row["cursos"] ?? 0),
            "promedio" => round((float) ($row["promedio"] ?? 0), 2),
        ];
    }

    /** Construye el filtro SQL según el periodo. */
    private static function filtroPeriodo(string $periodo): string
    {
        if ($periodo === "month") {
            return " AND fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ";
        }
        if ($periodo === "quarter") {
            return " AND fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) ";
        }
        if ($periodo === "year") {
            return " AND fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) ";
        }
        return "";
    }
}
