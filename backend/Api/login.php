<?php



header("Content-Type: application/json; charset=UTF-8");

session_start();



if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Método no permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}



require_once __DIR__ . "/../Config/database.php";

try {

    $pdo = Database::connect();

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "No se pudo conectar con la base de datos."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}



$input = json_decode(
    file_get_contents("php://input"),
    true
);

if (!is_array($input)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Los datos enviados no tienen un formato JSON válido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}



$email = isset($input["email"])
    ? trim(strtolower($input["email"]))
    : "";

$password = isset($input["password"])
    ? $input["password"]
    : "";



if ($email === "" || $password === "") {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Correo y contraseña son obligatorios."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "El correo electrónico no es válido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}



try {

    $sql = "
        SELECT
            id,
            nombres,
            apellidos,
            correo,
            password,
            rol,
            estado
        FROM usuarios
        WHERE correo = :correo
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":correo" => $email
    ]);

    $user = $stmt->fetch();

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Error al consultar el usuario."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}



if (!$user) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Correo o contraseña incorrectos."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}



if ($user["estado"] !== "activo") {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Tu cuenta está inactiva. Contacta al administrador."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}



if (!password_verify($password, $user["password"])) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Correo o contraseña incorrectos."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}



session_regenerate_id(true);



$_SESSION["user_id"] = $user["id"];
$_SESSION["nombres"] = $user["nombres"];
$_SESSION["apellidos"] = $user["apellidos"];
$_SESSION["correo"] = $user["correo"];
$_SESSION["rol"] = $user["rol"];
$_SESSION["estado"] = $user["estado"];
$_SESSION["authenticated"] = true;



echo json_encode([
    "success" => true,
    "message" => "Inicio de sesión correcto.",
    "user" => [
        "id" => $user["id"],
        "nombres" => $user["nombres"],
        "apellidos" => $user["apellidos"],
        "correo" => $user["correo"],
        "rol" => $user["rol"],
        "estado" => $user["estado"]
    ]
], JSON_UNESCAPED_UNICODE);

exit;
