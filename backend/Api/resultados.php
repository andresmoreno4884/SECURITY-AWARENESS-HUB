<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();
require_once __DIR__ . "/../Config/database.php";

function responderResultados(bool $success, string $message, array $data = [], int $status = 200): void
{
    http_response_code($status);
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SESSION["authenticated"] ?? false) !== true) {
    responderResultados(false, "No hay una sesión activa.", [], 401);
}

if (($_SESSION["rol"] ?? "") !== "admin") {
    responderResultados(false, "No tienes permisos para consultar resultados.", [], 403);
}

try {
    $pdo = Database::connect();
} catch (Throwable $error) {
    responderResultados(false, "No se pudo conectar con la base de datos.", [], 500);
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    responderResultados(false, "Método no permitido.", [], 405);
}

try {
    $search = trim($_GET["search"] ?? "");
    $cursoId = (int)($_GET["curso_id"] ?? 0);
    $evaluacionId = (int)($_GET["evaluacion_id"] ?? 0);
    $aprobado = trim($_GET["aprobado"] ?? "");

    $sql = "
        SELECT
            r.id,
            r.usuario_id,
            r.evaluacion_id,
            r.puntuacion,
            r.porcentaje,
            r.preguntas_totales,
            r.preguntas_correctas,
            r.preguntas_incorrectas,
            r.aprobado,
            r.fecha_resultado,
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
        $sql .= "
            AND (
                CONCAT(u.nombres, ' ', u.apellidos) LIKE :searchUsuario
                OR u.correo LIKE :searchCorreo
            )
        ";
        $searchValue = "%{$search}%";
        $params[":searchUsuario"] = $searchValue;
        $params[":searchCorreo"] = $searchValue;
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
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $resultados = $statement->fetchAll(PDO::FETCH_ASSOC);

    $stats = $pdo->query("SELECT COUNT(*) AS total, COALESCE(AVG(porcentaje), 0) AS promedio, SUM(aprobado = 'si') AS aprobados FROM resultados")->fetch(PDO::FETCH_ASSOC);

    responderResultados(true, "Resultados cargados correctamente.", [
        "resultados" => $resultados,
        "total" => (int)($stats["total"] ?? 0),
        "promedio" => round((float)($stats["promedio"] ?? 0), 2),
        "aprobados" => (int)($stats["aprobados"] ?? 0)
    ]);
} catch (PDOException $error) {
    responderResultados(false, "Error al consultar los resultados.", [], 500);
}
