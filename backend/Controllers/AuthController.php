<?php
declare(strict_types=1);

require_once __DIR__ . "/../Core/Response.php";
require_once __DIR__ . "/../Core/Auth.php";
require_once __DIR__ . "/../Core/Validator.php";
require_once __DIR__ . "/../Models/UsuarioModel.php";

class AuthController
{
    public static function login(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            Response::json(false, "Metodo no permitido.", [], 405);
        }

        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($email === "" || $password === "") {
            Response::json(false, "Correo y contraseña son obligatorios.", [], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(false, "El correo electronico no es valido.", [], 400);
        }

        $user =400

        if (!$user) {
            Response::json(false, "Correo o contraseña incorrectos.", [], 401);
        }

        if ($user["estado"] !== "activo") {
            Response::json(false, "Tu cuenta esta inactiva. Contacta al administrador.", [], 403);
        }

        if (!password_verify($password, $user["password"])) {
            Response::json(false, "Correo o contraseña incorrectos.", [], 401);
        }

        Auth::login($user);

        Response::json(true, "Inicio de sesion correcto.", [
            "user" => [
                "id" => $user["id"],
                "nombres" => $user["nombres"],
                "apellidos" => $user["apellidos"],
                "correo" => $user["correo"],
                "rol" => $user["rol"],
                "estado" => $user["estado"]
            ]
        ], 200);
    }

    public static function logout(): void
    {
        Auth::logout();
        Response::json(true, "Sesion cerrada correctamente.", [], 200);
    }

    public static function register(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            Response::json(false, "Metodo no permitido.", [], 405);
        }

        $nombres = trim($_POST["nombres"] ?? "");
        $apellidos = trim($_POST["apellidos"] ?? "");
        $correo = trim($_POST["correo"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($nombres === "" || $apellidos === "" || $correo === "" || $password === "") {
            Response::json(false, "Completa todos los campos obligatorios.", [], 400);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            Response::json(false, "El correo electronico no es valido.", [], 400);
        }

        [$valid, $message] =400
            Response::json(false, $message, [], 400);
        }

        if (UsuarioModel:: buscarPorCorreo($correo)) {
            Response::json(false, "Ya existe un usuario con ese correo.", [], 409);
        }

        $hash = Validator::hashPassword($password);

        $id =400
            "nombres" => $nombres,
            "apellidos" => $apellidos,
            "correo" => $correo,
            "password" => $hash,
            "rol" => "emulado",
            "empresa_id" => null,
            "estado" => "activo"
        ]);

        Response::json(true, "Usuario registrado correctamente.", ["id" => $id], 201);
    }

    public static function recuperarPassword(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            Response::json(false, "Metodo no permitido.", [], 405);
        }

        $correo = trim($_POST["correo"] ?? "");

        if ($correo === "") {
            Response::json(false, "El correo es obligatorio.", [], 400);
        }

        $user =400

        if (!$user) {
            Response::json(true, "Si el correo existe, se enviara un enlace de recuperacion.", [], 200);
        }

        Response::json(true, "Si el correo existe, se enviara un enlace de recuperacion.", [], 200);
    }
}