<?php
declare(strict_types=1);

/**
 * Modelo de acceso a datos de la tabla `usuarios`.
 */
class UsuarioModel
{
    /** Todos los usuarios con el nombre de su empresa. */
    public static function all(): array
    {
        $pdo = Database::connect();

        $sql = "SELECT u.*, e.nombre AS empresaNombre
                FROM usuarios u
                LEFT JOIN empresas e ON e.id = u.empresa_id
                ORDER BY u.id DESC";

        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Busca un usuario por id (con empresa). */
    public static function find(int $id): ?array
    {
        $pdo = Database::connect();

        $sql = "SELECT u.*, e.nombre AS empresaNombre
                FROM usuarios u
                LEFT JOIN empresas e ON e.id = u.empresa_id
                WHERE u.id = :id
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Listado administrativo: sólo rol 'usuario', con filtros de búsqueda
     * y estado. Réplica exacta del endpoint usuarios.php (GET).
     */
    public static function listarAdministrados(string $search = "", string $estado = ""): array
    {
        $pdo = Database::connect();

        $sql = "SELECT id, nombres, apellidos, correo, rol, estado, fecha_registro
                FROM usuarios
                WHERE rol = 'usuario'";

        $params = [];

        if ($search !== "") {
            $sql .= " AND (
                        nombres LIKE :searchNombres
                        OR apellidos LIKE :searchApellidos
                        OR correo LIKE :searchCorreo
                     )";
            $value = "%" . $search . "%";
            $params[":searchNombres"] = $value;
            $params[":searchApellidos"] = $value;
            $params[":searchCorreo"] = $value;
        }

        if ($estado !== "" && in_array($estado, ["activo", "inactivo"], true)) {
            $sql .= " AND estado = :estado";
            $params[":estado"] = $estado;
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Busca un usuario por correo (datos completos, incluye password). */
    public static function buscarPorCorreo(string $correo): ?array
    {
        $pdo = Database::connect();

        $sql = "SELECT id, nombres, apellidos, correo, password, rol, empresa_id, estado
                FROM usuarios
                WHERE correo = :correo
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([":correo" => $correo]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Crea un usuario y devuelve el id insertado. */
    public static function create(array $data): int
    {
        $pdo = Database::connect();

        $sql = "INSERT INTO usuarios
                    (nombres, apellidos, correo, password, rol, empresa_id, estado)
                VALUES
                    (:nombres, :apellidos, :correo, :password, :rol, :empresa_id, :estado)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nombres"    => $data["nombres"],
            ":apellidos"  => $data["apellidos"],
            ":correo"     => $data["correo"],
            ":password"   => $data["password"],
            ":rol"        => $data["rol"] ?? "usuario",
            ":empresa_id" => $data["empresa_id"] ?? null,
            ":estado"     => $data["estado"] ?? "activo",
        ]);

        return (int) $pdo->lastInsertId();
    }

    /** Actualiza los datos editables de un usuario. */
    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connect();

        $sql = "UPDATE usuarios
                SET nombres = :nombres,
                    apellidos = :apellidos,
                    correo = :correo,
                    rol = :rol,
                    empresa_id = :empresa_id,
                    estado = :estado
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ":id"          => $id,
            ":nombres"     => $data["nombres"],
            ":apellidos"   => $data["apellidos"],
            ":correo"      => $data["correo"],
            ":rol"         => $data["rol"],
            ":empresa_id"  => $data["empresa_id"] ?? null,
            ":estado"      => $data["estado"],
        ]);
    }

    /** Actualiza únicamente la contraseña (hash) de un usuario. */
    public static function updatePassword(int $id, string $passwordHash): bool
    {
        $pdo = Database::connect();

        $sql = "UPDATE usuarios SET password = :password WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ":password" => $passwordHash,
            ":id"       => $id,
        ]);
    }

    /** Cambia el estado de un usuario. */
    public static function cambiarEstado(int $id, string $estado): bool
    {
        $pdo = Database::connect();

        $sql = "UPDATE usuarios SET estado = :estado WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ":estado" => $estado,
            ":id"     => $id,
        ]);
    }

    /** Elimina un usuario. */
    public static function delete(int $id): bool
    {
        $pdo = Database::connect();

        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([":id" => $id]);
    }
}
