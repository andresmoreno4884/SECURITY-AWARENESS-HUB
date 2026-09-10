<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();
require_once __DIR__ . "/../Config/database.php";

function responderAsignaciones(bool $success, string $message, array $data = [], int $status = 200): void
{
    http_response_code($status);
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SESSION["authenticated"] ?? false) !== true) {
    responderAsignaciones(false, "No hay una sesión activa.", [], 401);
}

if (($_SESSION["rol"] ?? "") !== "admin") {
    responderAsignaciones(false, "No tienes permisos para administrar asignaciones.", [], 403);
}

try {
    $pdo = Database::connect();
} catch (Throwable $error) {
    responderAsignaciones(false, "No se pudo conectar con la base de datos.", [], 500);
}

$method = $_SERVER["REQUEST_METHOD"];
$input = json_decode(file_get_contents("php://input"), true);
$input = is_array($input) ? $input : [];

if ($method === "GET") {
    try {
        $search = trim($_GET["search"] ?? "");
        $estado = trim($_GET["estado"] ?? "");
        $tipo = trim($_GET["tipo"] ?? "");

        $sql = "
            SELECT
                a.id,
                a.usuario_id,
                a.curso_id,
                a.evaluacion_id,
                a.tipo,
                a.fecha_asignacion,
                a.estado,
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
            $sql .= "
                AND (
                    CONCAT(u.nombres, ' ', u.apellidos) LIKE :searchUsuario
                    OR c.titulo LIKE :searchCurso
                    OR e.titulo LIKE :searchEvaluacion
                )
            ";
            $searchValue = "%{$search}%";
            $params[":searchUsuario"] = $searchValue;
            $params[":searchCurso"] = $searchValue;
            $params[":searchEvaluacion"] = $searchValue;
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
        $statement = $pdo->prepare($sql);
        $statement->execute($params);
        $asignaciones = $statement->fetchAll(PDO::FETCH_ASSOC);

        $stats = $pdo->query("SELECT COUNT(*) AS total, SUM(estado IN ('pendiente', 'progreso')) AS activas, SUM(estado = 'completado') AS completadas FROM asignaciones")->fetch(PDO::FETCH_ASSOC);

        responderAsignaciones(true, "Asignaciones cargadas correctamente.", [
            "asignaciones" => $asignaciones,
            "total" => (int)($stats["total"] ?? 0),
            "activas" => (int)($stats["activas"] ?? 0),
            "completadas" => (int)($stats["completadas"] ?? 0)
        ]);
    } catch (PDOException $error) {
        responderAsignaciones(false, "Error al consultar las asignaciones.", [], 500);
    }
}

if ($method === "POST" || $method === "PUT") {
    $id = (int)($input["id"] ?? 0);
    $usuarioId = (int)($input["usuario_id"] ?? 0);
    $cursoId = (int)($input["curso_id"] ?? 0);
    $evaluacionId = (int)($input["evaluacion_id"] ?? 0);
    $tipo = trim($input["tipo"] ?? "curso");
    $fecha = trim($input["fecha_asignacion"] ?? date("Y-m-d"));
    $estado = trim($input["estado"] ?? "pendiente");

    if ($usuarioId <= 0 || !in_array($tipo, ["curso", "evaluacion"], true) || !in_array($estado, ["pendiente", "progreso", "completado"], true)) {
        responderAsignaciones(false, "Los datos de la asignación no son válidos.", [], 400);
    }

    if ($tipo === "curso" && $cursoId <= 0) {
        responderAsignaciones(false, "Debes seleccionar un curso.", [], 400);
    }

    if ($tipo === "evaluacion" && $evaluacionId <= 0) {
        responderAsignaciones(false, "Debes seleccionar una evaluación.", [], 400);
    }

    if ($tipo === "evaluacion") {
        $courseFromEvaluation = $pdo->prepare("SELECT curso_id FROM evaluaciones WHERE id = :id AND estado = 'activo' LIMIT 1");
        $courseFromEvaluation->execute([":id" => $evaluacionId]);
        $cursoId = (int)($courseFromEvaluation->fetchColumn() ?: 0);

        if ($cursoId <= 0) {
            responderAsignaciones(false, "La evaluación seleccionada no existe o está inactiva.", [], 404);
        }
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        responderAsignaciones(false, "La fecha de asignación no es válida.", [], 400);
    }

    try {
        if ($method === "POST") {
            $statement = $pdo->prepare("INSERT INTO asignaciones (usuario_id, curso_id, evaluacion_id, tipo, fecha_asignacion, estado) VALUES (:usuario_id, :curso_id, :evaluacion_id, :tipo, :fecha, :estado)");
            $statement->execute([
                ":usuario_id" => $usuarioId,
                ":curso_id" => $cursoId,
                ":evaluacion_id" => $evaluacionId > 0 ? $evaluacionId : null,
                ":tipo" => $tipo,
                ":fecha" => $fecha,
                ":estado" => $estado
            ]);
            responderAsignaciones(true, "Asignación creada correctamente.", ["id" => (int)$pdo->lastInsertId()], 201);
        }

        if ($id <= 0) {
            responderAsignaciones(false, "El ID de asignación no es válido.", [], 400);
        }

        $statement = $pdo->prepare("UPDATE asignaciones SET usuario_id = :usuario_id, curso_id = :curso_id, evaluacion_id = :evaluacion_id, tipo = :tipo, fecha_asignacion = :fecha, estado = :estado WHERE id = :id");
        $statement->execute([
            ":usuario_id" => $usuarioId,
            ":curso_id" => $cursoId,
            ":evaluacion_id" => $evaluacionId > 0 ? $evaluacionId : null,
            ":tipo" => $tipo,
            ":fecha" => $fecha,
            ":estado" => $estado,
            ":id" => $id
        ]);
        responderAsignaciones(true, "Asignación actualizada correctamente.");
    } catch (PDOException $error) {
        $duplicate = $error->getCode() === "23000";
        responderAsignaciones(false, $duplicate ? "Esta asignación ya existe." : "No fue posible guardar la asignación.", [], $duplicate ? 409 : 500);
    }
}

if ($method === "PATCH") {
    $id = (int)($input["id"] ?? 0);
    $estado = trim($input["estado"] ?? "");

    if ($id <= 0 || !in_array($estado, ["pendiente", "progreso", "completado"], true)) {
        responderAsignaciones(false, "El estado de la asignación no es válido.", [], 400);
    }

    $statement = $pdo->prepare("UPDATE asignaciones SET estado = :estado WHERE id = :id");
    $statement->execute([":estado" => $estado, ":id" => $id]);
    responderAsignaciones(true, "Estado actualizado correctamente.");
}

if ($method === "DELETE") {
    $id = (int)($input["id"] ?? 0);

    if ($id <= 0) {
        responderAsignaciones(false, "El ID de asignación no es válido.", [], 400);
    }

    $statement = $pdo->prepare("DELETE FROM asignaciones WHERE id = :id");
    $statement->execute([":id" => $id]);
    responderAsignaciones(true, "Asignación eliminada correctamente.");
}

responderAsignaciones(false, "Método no permitido.", [], 405);
