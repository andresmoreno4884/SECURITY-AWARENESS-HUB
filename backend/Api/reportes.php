<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();
require_once __DIR__ . "/../Config/database.php";

function responderReportes(bool $success, string $message, array $data = [], int $status = 200): void
{
    http_response_code($status);
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SESSION["authenticated"] ?? false) !== true) {
    responderReportes(false, "No hay una sesión activa.", [], 401);
}

if (($_SESSION["rol"] ?? "") !== "admin") {
    responderReportes(false, "No tienes permisos para generar reportes.", [], 403);
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    responderReportes(false, "Método no permitido.", [], 405);
}

try {
    $pdo = Database::connect();
} catch (Throwable $error) {
    responderReportes(false, "No se pudo conectar con la base de datos.", [], 500);
}

$tipo = trim($_GET["tipo"] ?? "");
$periodo = trim($_GET["periodo"] ?? "all");
$periodosValidos = ["all", "month", "quarter", "year"];

if (!in_array($tipo, ["usuarios", "cursos", "evaluaciones", "resultados", "capacitacion"], true)) {
    responderReportes(false, "El tipo de reporte no es válido.", [], 400);
}

if (!in_array($periodo, $periodosValidos, true)) {
    responderReportes(false, "El periodo no es válido.", [], 400);
}

$fechaFiltro = "";
if ($periodo === "month") {
    $fechaFiltro = " AND fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ";
} elseif ($periodo === "quarter") {
    $fechaFiltro = " AND fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) ";
} elseif ($periodo === "year") {
    $fechaFiltro = " AND fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) ";
}

try {
    if ($tipo === "usuarios") {
        $sql = "SELECT id, CONCAT(nombres, ' ', apellidos) AS usuario, correo, rol, estado, fecha_registro FROM usuarios WHERE 1 = 1" . $fechaFiltro . " ORDER BY fecha_registro DESC";
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $columns = ["usuario" => "Usuario", "correo" => "Correo", "rol" => "Rol", "estado" => "Estado", "fecha_registro" => "Registro"];
    } elseif ($tipo === "cursos") {
        $sql = "SELECT id, titulo, nivel_dificultad, estado, fecha_creacion FROM cursos WHERE 1 = 1" . str_replace("fecha_registro", "fecha_creacion", $fechaFiltro) . " ORDER BY fecha_creacion DESC";
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $columns = ["titulo" => "Curso", "nivel_dificultad" => "Nivel", "estado" => "Estado", "fecha_creacion" => "Creación"];
    } elseif ($tipo === "evaluaciones") {
        $sql = "SELECT e.id, e.titulo AS evaluacion, c.titulo AS curso, e.estado, e.fecha_creacion FROM evaluaciones e LEFT JOIN cursos c ON c.id = e.curso_id WHERE 1 = 1" . str_replace("fecha_registro", "e.fecha_creacion", $fechaFiltro) . " ORDER BY e.fecha_creacion DESC";
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $columns = ["evaluacion" => "Evaluación", "curso" => "Curso", "estado" => "Estado", "fecha_creacion" => "Creación"];
    } else {
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
    }

    $stats = $pdo->query("SELECT (SELECT COUNT(*) FROM usuarios WHERE rol = 'usuario') AS usuarios, (SELECT COUNT(*) FROM cursos) AS cursos, COALESCE((SELECT AVG(porcentaje) FROM resultados), 0) AS promedio")->fetch(PDO::FETCH_ASSOC);

    responderReportes(true, "Reporte generado correctamente.", [
        "tipo" => $tipo,
        "periodo" => $periodo,
        "columnas" => $columns,
        "filas" => $rows,
        "estadisticas" => [
            "usuarios" => (int)$stats["usuarios"],
            "cursos" => (int)$stats["cursos"],
            "promedio" => round((float)$stats["promedio"], 2)
        ],
        "total" => count($rows)
    ]);
} catch (PDOException $error) {
    responderReportes(false, "No fue posible generar el reporte.", [], 500);
}
