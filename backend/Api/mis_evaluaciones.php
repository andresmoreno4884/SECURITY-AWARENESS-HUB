<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();
require_once __DIR__ . "/../Config/database.php";

function responderMisEvaluaciones(bool $success, string $message, array $data = [], int $status = 200): void
{
    http_response_code($status);
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SESSION["authenticated"] ?? false) !== true) {
    responderMisEvaluaciones(false, "No hay una sesión activa.", [], 401);
}

try {
    $pdo = Database::connect();
} catch (Throwable $error) {
    error_log($error->getMessage());
    responderMisEvaluaciones(false, "No se pudo conectar con la base de datos.", [], 500);
}

$method = $_SERVER["REQUEST_METHOD"];
$input = json_decode(file_get_contents("php://input"), true);
$input = is_array($input) ? $input : [];
$userId = (int)$_SESSION["user_id"];

if ($method === "GET") {
    if (isset($_GET["resultado"])) {
        $statement = $pdo->prepare("
            SELECT porcentaje, preguntas_totales, preguntas_correctas,
                   preguntas_incorrectas, aprobado, fecha_resultado
            FROM resultados
            WHERE usuario_id = :usuario_id
            ORDER BY id DESC
            LIMIT 1
        ");
        $statement->execute([":usuario_id" => $userId]);
        responderMisEvaluaciones(true, "Resultado cargado correctamente.", [
            "resultado" => $statement->fetch(PDO::FETCH_ASSOC) ?: null
        ]);
    }

    $courseId = (int)($_GET["curso_id"] ?? 0);
    $statement = $pdo->prepare("
        SELECT e.id, e.curso_id, e.titulo, e.descripcion, e.porcentaje_aprobacion
        FROM evaluaciones e
        INNER JOIN asignaciones a ON a.curso_id = e.curso_id
        WHERE a.usuario_id = :usuario_id
          AND a.tipo = 'curso'
          AND a.estado <> 'completado'
          AND e.estado = 'activo'
          AND (:curso_id = 0 OR e.curso_id = :curso_id_filter)
        ORDER BY e.id DESC
        LIMIT 1
    ");
    $statement->execute([
        ":usuario_id" => $userId,
        ":curso_id" => $courseId,
        ":curso_id_filter" => $courseId
    ]);
    $evaluacion = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$evaluacion) {
        responderMisEvaluaciones(true, "No hay una evaluación disponible.", ["evaluacion" => null]);
    }

    $questions = $pdo->prepare("SELECT id, enunciado, orden FROM preguntas WHERE evaluacion_id = :evaluacion_id ORDER BY orden, id");
    $questions->execute([":evaluacion_id" => $evaluacion["id"]]);
    $evaluacion["preguntas"] = $questions->fetchAll(PDO::FETCH_ASSOC);

    $options = $pdo->prepare("SELECT id, pregunta_id, texto, orden FROM opciones WHERE pregunta_id = :pregunta_id ORDER BY orden, id");
    foreach ($evaluacion["preguntas"] as &$question) {
        $options->execute([":pregunta_id" => $question["id"]]);
        $question["opciones"] = $options->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($question);

    responderMisEvaluaciones(true, "Evaluación cargada correctamente.", ["evaluacion" => $evaluacion]);
}

if ($method === "POST") {
    $evaluationId = (int)($input["evaluacion_id"] ?? 0);
    $answers = $input["respuestas"] ?? [];

    if ($evaluationId <= 0 || !is_array($answers) || count($answers) === 0) {
        responderMisEvaluaciones(false, "Debes responder la evaluación.", [], 400);
    }

    $check = $pdo->prepare("
        SELECT e.id, e.curso_id, e.porcentaje_aprobacion
        FROM evaluaciones e
        INNER JOIN asignaciones a ON a.curso_id = e.curso_id
        WHERE e.id = :evaluacion_id
          AND a.usuario_id = :usuario_id
          AND a.tipo = 'curso'
          AND e.estado = 'activo'
        LIMIT 1
    ");
    $check->execute([":evaluacion_id" => $evaluationId, ":usuario_id" => $userId]);
    $evaluation = $check->fetch(PDO::FETCH_ASSOC);

    if (!$evaluation) {
        responderMisEvaluaciones(false, "La evaluación no está asignada a tu usuario.", [], 403);
    }

    $correctAnswers = $pdo->prepare("
        SELECT p.id AS pregunta_id, o.id AS opcion_id
        FROM preguntas p
        INNER JOIN opciones o ON o.pregunta_id = p.id
        WHERE p.evaluacion_id = :evaluacion_id AND o.es_correcta = 1
    ");
    $correctAnswers->execute([":evaluacion_id" => $evaluationId]);
    $correct = [];
    foreach ($correctAnswers->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $correct[(string)$row["pregunta_id"]] = (int)$row["opcion_id"];
    }

    $total = count($correct);
    $right = 0;
    foreach ($answers as $questionId => $optionId) {
        if (isset($correct[(string)$questionId]) && (int)$optionId === $correct[(string)$questionId]) {
            $right++;
        }
    }

    if ($total === 0) {
        responderMisEvaluaciones(false, "La evaluación no tiene preguntas válidas.", [], 400);
    }

    $percentage = round(($right / $total) * 100, 2);
    $passed = $percentage >= (int)$evaluation["porcentaje_aprobacion"] ? "si" : "no";
    $wrong = $total - $right;

    $pdo->beginTransaction();
    try {
        $insert = $pdo->prepare("INSERT INTO resultados (usuario_id, evaluacion_id, puntuacion, porcentaje, preguntas_totales, preguntas_correctas, preguntas_incorrectas, aprobado) VALUES (:usuario_id, :evaluacion_id, :puntuacion, :porcentaje, :totales, :correctas, :incorrectas, :aprobado)");
        $insert->execute([
            ":usuario_id" => $userId,
            ":evaluacion_id" => $evaluationId,
            ":puntuacion" => $percentage,
            ":porcentaje" => $percentage,
            ":totales" => $total,
            ":correctas" => $right,
            ":incorrectas" => $wrong,
            ":aprobado" => $passed
        ]);
        $update = $pdo->prepare("UPDATE asignaciones SET estado = 'completado' WHERE usuario_id = :usuario_id AND curso_id = :curso_id AND tipo = 'curso'");
        $update->execute([":usuario_id" => $userId, ":curso_id" => $evaluation["curso_id"]]);
        $pdo->commit();
    } catch (Throwable $error) {
        $pdo->rollBack();
        error_log($error->getMessage());
        responderMisEvaluaciones(false, "No fue posible guardar el resultado.", [], 500);
    }

    responderMisEvaluaciones(true, "Evaluación calificada correctamente.", [
        "resultado" => [
            "porcentaje" => $percentage,
            "preguntas_totales" => $total,
            "preguntas_correctas" => $right,
            "preguntas_incorrectas" => $wrong,
            "aprobado" => $passed
        ]
    ], 201);
}

responderMisEvaluaciones(false, "Método no permitido.", [], 405);
