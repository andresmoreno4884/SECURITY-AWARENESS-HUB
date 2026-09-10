<?php

header("Content-Type: application/json; charset=UTF-8");

session_start();

require_once __DIR__ . "/../Config/database.php";

if (
    !isset($_SESSION["authenticated"]) ||
    $_SESSION["authenticated"] !== true
) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "No hay una sesión activa."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "admin"
) {
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "No tienes permisos para administrar empresas."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

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

$method = $_SERVER["REQUEST_METHOD"];

$input = json_decode(
    file_get_contents("php://input"),
    true
);

if (!is_array($input)) {
    $input = [];
}

if ($method === "GET") {

    try {

        $search = isset($_GET["search"])
            ? trim($_GET["search"])
            : "";

        $estado = isset($_GET["estado"])
            ? trim($_GET["estado"])
            : "";

        $tipo = isset($_GET["tipo"])
            ? trim($_GET["tipo"])
            : "";

        $sql = "
            SELECT
                id,
                nombre,
                nit,
                correo,
                telefono,
                direccion,
                tipo,
                estado,
                fecha_registro
            FROM empresas
            WHERE 1 = 1
        ";

        $params = [];

        if ($search !== "") {

            $sql .= "
                AND (
                        nombre LIKE :searchNombre
                        OR nit LIKE :searchNit
                        OR correo LIKE :searchCorreo
                )
            ";

                $searchValue = "%" . $search . "%";
                $params[":searchNombre"] = $searchValue;
                $params[":searchNit"] = $searchValue;
                $params[":searchCorreo"] = $searchValue;
        }

        if (
            $estado !== "" &&
            in_array($estado, ["activo", "inactivo"], true)
        ) {

            $sql .= "
                AND estado = :estado
            ";

            $params[":estado"] = $estado;
        }

        if (
            $tipo !== "" &&
            in_array($tipo, ["privada", "publica", "mixta"], true)
        ) {

            $sql .= "
                AND tipo = :tipo
            ";

            $params[":tipo"] = $tipo;
        }

        $sql .= "
            ORDER BY id DESC
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute($params);

        $empresas = $stmt->fetchAll();

        $statsStmt = $pdo->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) AS activas,
                SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) AS inactivas
            FROM empresas
        ");

        $stats = $statsStmt->fetch();

        echo json_encode([
            "success" => true,
            "message" => "Empresas cargadas correctamente.",
            "empresas" => $empresas,
            "total" => (int) ($stats["total"] ?? 0),
            "activas" => (int) ($stats["activas"] ?? 0),
            "inactivas" => (int) ($stats["inactivas"] ?? 0),
            "total_mostradas" => count($empresas)
        ], JSON_UNESCAPED_UNICODE);

        exit;

    } catch (PDOException $e) {

        error_log($e->getMessage());

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Error al consultar las empresas."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

if ($method === "POST") {

    $nombre = isset($input["nombre"])
        ? trim($input["nombre"])
        : "";

    $nit = isset($input["nit"])
        ? trim($input["nit"])
        : "";

    $correo = isset($input["correo"])
        ? trim(strtolower($input["correo"]))
        : "";

    $telefono = isset($input["telefono"])
        ? trim($input["telefono"])
        : "";

    $direccion = isset($input["direccion"])
        ? trim($input["direccion"])
        : "";

    $tipo = isset($input["tipo"])
        ? trim($input["tipo"])
        : "privada";

    $estado = isset($input["estado"])
        ? trim($input["estado"])
        : "activo";

    if (
        $nombre === "" ||
        $nit === "" ||
        $correo === ""
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Los campos nombre, NIT y correo son obligatorios."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "El correo electrónico no es válido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!in_array($tipo, ["privada", "publica", "mixta"], true)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "El tipo de empresa no es válido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!in_array($estado, ["activo", "inactivo"], true)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "El estado de la empresa no es válido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    try {

        $checkNit = $pdo->prepare("
            SELECT id
            FROM empresas
            WHERE nit = :nit
            LIMIT 1
        ");

        $checkNit->execute([
            ":nit" => $nit
        ]);

        if ($checkNit->fetch()) {

            http_response_code(409);

            echo json_encode([
                "success" => false,
                "message" => "El NIT ya está registrado."
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO empresas (
                nombre,
                nit,
                correo,
                telefono,
                direccion,
                tipo,
                estado
            )
            VALUES (
                :nombre,
                :nit,
                :correo,
                :telefono,
                :direccion,
                :tipo,
                :estado
            )
        ");

        $stmt->execute([
            ":nombre" => $nombre,
            ":nit" => $nit,
            ":correo" => $correo,
            ":telefono" => $telefono !== "" ? $telefono : null,
            ":direccion" => $direccion !== "" ? $direccion : null,
            ":tipo" => $tipo,
            ":estado" => $estado
        ]);

        $id = $pdo->lastInsertId();

        echo json_encode([
            "success" => true,
            "message" => "Empresa creada correctamente.",
            "empresa" => [
                "id" => (int) $id,
                "nombre" => $nombre,
                "nit" => $nit,
                "correo" => $correo,
                "telefono" => $telefono,
                "direccion" => $direccion,
                "tipo" => $tipo,
                "estado" => $estado
            ]
        ], JSON_UNESCAPED_UNICODE);

        exit;

    } catch (PDOException $e) {

        error_log($e->getMessage());

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "No fue posible crear la empresa."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

if ($method === "PUT") {

    $id = isset($input["id"])
        ? (int) $input["id"]
        : 0;

    $nombre = isset($input["nombre"])
        ? trim($input["nombre"])
        : "";

    $nit = isset($input["nit"])
        ? trim($input["nit"])
        : "";

    $correo = isset($input["correo"])
        ? trim(strtolower($input["correo"]))
        : "";

    $telefono = isset($input["telefono"])
        ? trim($input["telefono"])
        : "";

    $direccion = isset($input["direccion"])
        ? trim($input["direccion"])
        : "";

    $tipo = isset($input["tipo"])
        ? trim($input["tipo"])
        : "";

    $estado = isset($input["estado"])
        ? trim($input["estado"])
        : "";

    if (
        $id <= 0 ||
        $nombre === "" ||
        $nit === "" ||
        $correo === ""
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Los datos de la empresa son incompletos."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "El correo electrónico no es válido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!in_array($tipo, ["privada", "publica", "mixta"], true)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "El tipo de empresa no es válido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!in_array($estado, ["activo", "inactivo"], true)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "El estado de la empresa no es válido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    try {

        $find = $pdo->prepare("
            SELECT id
            FROM empresas
            WHERE id = :id
            LIMIT 1
        ");

        $find->execute([
            ":id" => $id
        ]);

        if (!$find->fetch()) {

            http_response_code(404);

            echo json_encode([
                "success" => false,
                "message" => "La empresa no existe."
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $checkNit = $pdo->prepare("
            SELECT id
            FROM empresas
            WHERE nit = :nit
            AND id <> :id
            LIMIT 1
        ");

        $checkNit->execute([
            ":nit" => $nit,
            ":id" => $id
        ]);

        if ($checkNit->fetch()) {

            http_response_code(409);

            echo json_encode([
                "success" => false,
                "message" => "El NIT ya pertenece a otra empresa."
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE empresas
            SET
                nombre = :nombre,
                nit = :nit,
                correo = :correo,
                telefono = :telefono,
                direccion = :direccion,
                tipo = :tipo,
                estado = :estado
            WHERE id = :id
        ");

        $stmt->execute([
            ":nombre" => $nombre,
            ":nit" => $nit,
            ":correo" => $correo,
            ":telefono" => $telefono !== "" ? $telefono : null,
            ":direccion" => $direccion !== "" ? $direccion : null,
            ":tipo" => $tipo,
            ":estado" => $estado,
            ":id" => $id
        ]);

        echo json_encode([
            "success" => true,
            "message" => "Empresa actualizada correctamente."
        ], JSON_UNESCAPED_UNICODE);

        exit;

    } catch (PDOException $e) {

        error_log($e->getMessage());

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "No fue posible actualizar la empresa."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

if ($method === "PATCH") {

    $id = isset($input["id"])
        ? (int) $input["id"]
        : 0;

    $estado = isset($input["estado"])
        ? trim($input["estado"])
        : "";

    if ($id <= 0) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "ID de empresa inválido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!in_array($estado, ["activo", "inactivo"], true)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Estado inválido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    try {

        $find = $pdo->prepare("
            SELECT id
            FROM empresas
            WHERE id = :id
            LIMIT 1
        ");

        $find->execute([
            ":id" => $id
        ]);

        if (!$find->fetch()) {

            http_response_code(404);

            echo json_encode([
                "success" => false,
                "message" => "La empresa no existe."
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE empresas
            SET estado = :estado
            WHERE id = :id
        ");

        $stmt->execute([
            ":estado" => $estado,
            ":id" => $id
        ]);

        echo json_encode([
            "success" => true,
            "message" => "Estado de la empresa actualizado correctamente."
        ], JSON_UNESCAPED_UNICODE);

        exit;

    } catch (PDOException $e) {

        error_log($e->getMessage());

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "No fue posible actualizar el estado de la empresa."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

if ($method === "DELETE") {

    $id = isset($input["id"])
        ? (int) $input["id"]
        : 0;

    if ($id <= 0) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "ID de empresa inválido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    try {

        $find = $pdo->prepare("
            SELECT id
            FROM empresas
            WHERE id = :id
            LIMIT 1
        ");

        $find->execute([
            ":id" => $id
        ]);

        if (!$find->fetch()) {

            http_response_code(404);

            echo json_encode([
                "success" => false,
                "message" => "La empresa no existe."
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $stmt = $pdo->prepare("
            DELETE FROM empresas
            WHERE id = :id
        ");

        $stmt->execute([
            ":id" => $id
        ]);

        echo json_encode([
            "success" => true,
            "message" => "Empresa eliminada correctamente."
        ], JSON_UNESCAPED_UNICODE);

        exit;

    } catch (PDOException $e) {

        error_log($e->getMessage());

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "No fue posible eliminar la empresa."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

http_response_code(405);

echo json_encode([
    "success" => false,
    "message" => "Método HTTP no permitido."
], JSON_UNESCAPED_UNICODE);

exit;