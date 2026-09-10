<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();
require_once __DIR__ . "/../Config/database.php";

function responderMisCursos(bool $success, string $message, array $data = [], int $status = 200): void
{
    http_response_code($status);
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SESSION["authenticated"] ?? false) !== true) {
    responderMisCursos(false, "No hay una sesión activa.", [], 401);
}

try {
    $pdo = Database::connect();
    $statement = $pdo->prepare("
        SELECT
            a.id AS asignacion_id,
            a.estado AS estado_asignacion,
            a.fecha_asignacion,
            c.id,
            c.titulo,
            c.descripcion,
            c.nivel_dificultad,
            c.duracion,
            c.numero_modulos,
            c.porcentaje_aprobacion,
            c.estado
        FROM asignaciones a
        INNER JOIN cursos c ON c.id = a.curso_id
        WHERE a.usuario_id = :usuario_id
          AND a.tipo = 'curso'
        ORDER BY a.id DESC
    ");
    $statement->execute([
        ":usuario_id" => (int)$_SESSION["user_id"]
    ]);

    responderMisCursos(true, "Cursos asignados cargados correctamente.", [
        "cursos" => $statement->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (PDOException $error) {
    responderMisCursos(false, "No fue posible cargar los cursos asignados.", [], 500);
}
