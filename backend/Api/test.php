<?php



header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../Config/database.php";

try {

    $pdo = Database::connect();

    $stmt = $pdo->query("SELECT DATABASE() AS database_name");

    $result = $stmt->fetch();

    echo json_encode([
        "success" => true,
        "message" => "Conexión con MySQL exitosa",
        "database" => $result["database_name"]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Error al conectar con MySQL."
    ], JSON_UNESCAPED_UNICODE);
}
