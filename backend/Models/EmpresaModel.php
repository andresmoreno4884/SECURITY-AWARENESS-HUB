<?php
declare(strict_types=1);

/**
 * Modelo de acceso a datos de la tabla `empresas`.
 */
class EmpresaModel
{
    /** Listado con filtros (búsqueda, estado, tipo). Réplica de empresas.php (GET). */
    public static function listar(string $search = "", string $estado = "", string $tipo = ""): array
    {
        $pdo = Database::connect();

        $sql = "SELECT id, nombre, nit, correo, telefono, direccion, tipo, estado, fecha_registro
                FROM empresas
                WHERE 1 = 1";

        $params = [];

        if ($search !== "") {
            $sql .= " AND (
                        nombre LIKE :searchNombre
                        OR nit LIKE :searchNit
                        OR correo LIKE :searchCorreo
                     )";
            $value = "%" . $search . "%";
            $params[":searchNombre"] = $value;
            $params[":searchNit"] = $value;
            $params[":searchCorreo"] = $value;
        }

        if ($estado !== "" && in_array($estado, ["activo", "inactivo"], true)) {
            $sql .= " AND estado = :estado";
            $params[":estado"] = $estado;
        }

        if ($tipo !== "" && in_array($tipo, ["privada", "publica", "mixta"], true)) {
            $sql .= " AND tipo = :tipo";
            $params[":tipo"] = $tipo;
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Estadísticas globales de empresas. */
    public static function estadisticas(): array
    {
        $pdo = Database::connect();

        $row = $pdo->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) AS activas,
                SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) AS inactivas
            FROM empresas
        ")->fetch(PDO::FETCH_ASSOC);

        return [
            "total"      => (int) ($row["total"] ?? 0),
            "activas"    => (int) ($row["activas"] ?? 0),
            "inactivas"  => (int) ($row["inactivas"] ?? 0),
        ];
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM empresas WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Existe otra empresa (distinta de $exceptId) con ese NIT. */
    public static function existeNit(string $nit, ?int $exceptId = null): bool
    {
        $pdo = Database::connect();

        if ($exceptId === null) {
            $stmt = $pdo->prepare("SELECT id FROM empresas WHERE nit = :nit LIMIT 1");
            $stmt->execute([":nit" => $nit]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM empresas WHERE nit = :nit AND id <> :id LIMIT 1");
            $stmt->execute([":nit" => $nit, ":id" => $exceptId]);
        }

        return (bool) $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect();

        $sql = "INSERT INTO empresas
                    (nombre, nit, correo, telefono, direccion, tipo, estado)
                VALUES
                    (:nombre, :nit, :correo, :telefono, :direccion, :tipo, :estado)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nombre"    => $data["nombre"],
            ":nit"       => $data["nit"],
            ":correo"    => $data["correo"],
            ":telefono"  => ($data["telefono"] !== "" ? $data["telefono"] : null),
            ":direccion" => ($data["direccion"] !== "" ? $data["direccion"] : null),
            ":tipo"      => $data["tipo"] ?? "privada",
            ":estado"    => $data["estado"] ?? "activo",
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connect();

        $sql = "UPDATE empresas
                SET nombre = :nombre,
                    nit = :nit,
                    correo = :correo,
                    telefono = :telefono,
                    direccion = :direccion,
                    tipo = :tipo,
                    estado = :estado
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ":id"         => $id,
            ":nombre"     => $data["nombre"],
            ":nit"        => $data["nit"],
            ":correo"     => $data["correo"],
            ":telefono"   => ($data["telefono"] !== "" ? $data["telefono"] : null),
            ":direccion"  => ($data["direccion"] !== "" ? $data["direccion"] : null),
            ":tipo"       => $data["tipo"] ?? "privada",
            ":estado"     => $data["estado"] ?? "activo",
        ]);
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("UPDATE empresas SET estado = :estado WHERE id = :id");

        return $stmt->execute([
            ":estado" => $estado,
            ":id"     => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("DELETE FROM empresas WHERE id = :id");

        return $stmt->execute([":id" => $id]);
    }
}
