<?php
declare(strict_types=1);

/**
 * Modelo de acceso a datos de la tabla `cursos`.
 */
class CursoModel
{
    /** Todos los cursos (los más recientes primero). Réplica de courses.php (GET). */
    public static function listar(): array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            SELECT
                id, titulo, descripcion, nivel_dificultad, duracion,
                numero_modulos, porcentaje_aprobacion, estado, fecha_creacion
            FROM cursos
            ORDER BY fecha_creacion DESC, id DESC
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM cursos WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function existe(int $id): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => $id]);

        return (bool) $stmt->fetch();
    }

    public static function existeTitulo(string $titulo, ?int $exceptId = null): bool
    {
        $pdo = Database::connect();

        if ($exceptId === null) {
            $stmt = $pdo->prepare("SELECT id FROM cursos WHERE titulo = :titulo LIMIT 1");
            $stmt->execute([":titulo" => $titulo]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM cursos WHERE titulo = :titulo AND id <> :id LIMIT 1");
            $stmt->execute([":titulo" => $titulo, ":id" => $exceptId]);
        }

        return (bool) $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect();

        $sql = "INSERT INTO cursos
                    (titulo, descripcion, nivel_dificultad, duracion,
                     numero_modulos, porcentaje_aprobacion, estado)
                VALUES
                    (:titulo, :descripcion, :nivel, :duracion,
                     :modulos, :porcentaje, :estado)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":titulo"     => $data["titulo"],
            ":descripcion" => $data["descripcion"],
            ":nivel"      => $data["nivel_dificultad"],
            ":duracion"   => $data["duracion"],
            ":modulos"    => $data["numero_modulos"],
            ":porcentaje" => $data["porcentaje_aprobacion"],
            ":estado"     => $data["estado"],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connect();

        $sql = "UPDATE cursos
                SET titulo = :titulo,
                    descripcion = :descripcion,
                    nivel_dificultad = :nivel,
                    duracion = :duracion,
                    numero_modulos = :modulos,
                    porcentaje_aprobacion = :porcentaje,
                    estado = :estado
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ":id"         => $id,
            ":titulo"     => $data["titulo"],
            ":descripcion" => $data["descripcion"],
            ":nivel"      => $data["nivel_dificultad"],
            ":duracion"   => $data["duracion"],
            ":modulos"    => $data["numero_modulos"],
            ":porcentaje" => $data["porcentaje_aprobacion"],
            ":estado"     => $data["estado"],
        ]);
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("UPDATE cursos SET estado = :estado WHERE id = :id");

        return $stmt->execute([
            ":estado" => $estado,
            ":id"     => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("DELETE FROM cursos WHERE id = :id");

        return $stmt->execute([":id" => $id]);
    }
}
