<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();
require_once __DIR__ . "/../Config/database.php";

function responderRecuperacion(bool $success, string $message, array $data = [], int $status = 200): void
{
    http_response_code($status);
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responderRecuperacion(false, "Método no permitido.", [], 405);
}

$input = json_decode(file_get_contents("php://input"), true);
$input = is_array($input) ? $input : [];
$action = trim($input["action"] ?? "");

try {
    $pdo = Database::connect();
} catch (Throwable $error) {
    responderRecuperacion(false, "No se pudo conectar con la base de datos.", [], 500);
}

if ($action === "verify") {
    $correo = strtolower(trim($input["correo"] ?? ""));

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        responderRecuperacion(false, "Ingresa un correo electrónico válido.", [], 400);
    }

    $statement = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo AND estado = 'activo' LIMIT 1");
    $statement->execute([":correo" => $correo]);
    $usuario = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        responderRecuperacion(false, "No encontramos una cuenta con este correo.", [], 404);
    }

    $_SESSION["recovery_user_id"] = (int)$usuario["id"];
    $_SESSION["recovery_expires"] = time() + 900;
    responderRecuperacion(true, "Cuenta encontrada. Ahora crea una nueva contraseña.");
}

if ($action === "reset") {
    $usuarioId = (int)($_SESSION["recovery_user_id"] ?? 0);
    $expires = (int)($_SESSION["recovery_expires"] ?? 0);
    $password = (string)($input["password"] ?? "");

    if ($usuarioId <= 0 || $expires < time()) {
        unset($_SESSION["recovery_user_id"], $_SESSION["recovery_expires"]);
        responderRecuperacion(false, "La recuperación expiró. Verifica nuevamente tu correo.", [], 401);
    }

    if (strlen($password) < 8) {
        responderRecuperacion(false, "La contraseña debe tener mínimo 8 caracteres.", [], 400);
    }

    $statement = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id = :id AND estado = 'activo'");
    $statement->execute([
        ":password" => password_hash($password, PASSWORD_DEFAULT),
        ":id" => $usuarioId
    ]);

    unset($_SESSION["recovery_user_id"], $_SESSION["recovery_expires"]);
    responderRecuperacion(true, "Contraseña actualizada correctamente.");
}

responderRecuperacion(false, "Acción de recuperación no válida.", [], 400);
