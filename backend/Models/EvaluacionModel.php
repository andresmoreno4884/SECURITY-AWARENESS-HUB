<?php
declare(strict_types=1);

/**
 * Modelo de acceso a datos de las tablas `evaluaciones`, `preguntas` y `opciones`.
 */
class EvaluacionModel
{
    /** Listado de evaluaciones con el título del curso y total de preguntas. */
    public static function listar(): array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            SELECT
                e.id, e.curso_id, c.titulo AS curso_titulo, e.titulo,
                e.porcentaje_aprobacion, e.estado, e.fecha_creacion,
                (SELECT COUNT(*) FROM preguntas p WHERE p.evaluacion_id = e.id) AS total_preguntas
            FROM evaluaciones e
            INNER JOIN cursos c ON c.id = e.curso_id
            ORDER BY e.fecha_creacion DESC, e.id DESC
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Una evaluación con sus preguntas y opciones (para edición). */
    public static function findConPreguntas(int $id): ?array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            SELECT
                e.id, e.curso_id, c.titulo AS curso_titulo, e.titulo,
                e.descripcion, e.porcentaje_aprobacion, e.estado, e.fecha_creacion
            FROM evaluaciones e
            INNER JOIN cursos c ON c.id = e.curso_id
            WHERE e.id = :id
            LIMIT 1
        ");
        $stmt->execute([":id" => $id]);

        $evaluacion = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$evaluacion) {
            return null;
        }

        $stmtPreguntas = $pdo->prepare("
            SELECT id, enunciado, orden
            FROM preguntas
            WHERE evaluacion_id = :evaluacion_id
            ORDER BY orden ASC, id ASC
        ");
        $stmtPreguntas->execute([":evaluacion_id" => $id]);
        $preguntas = $stmtPreguntas->fetchAll(PDO::FETCH_ASSOC);

        $stmtOpciones = $pdo->prepare("
            SELECT id, texto, es_correcta, orden
            FROM opciones
            WHERE pregunta_id = :pregunta_id
            ORDER BY orden ASC, id ASC
        ");

        foreach ($preguntas as &$pregunta) {
            $stmtOpciones->execute([":pregunta_id" => $pregunta["id"]]);
            $pregunta["opciones"] = $stmtOpciones->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($pregunta);

        $evaluacion["preguntas"] = $preguntas;

        return $evaluacion;
    }

    public static function existe(int $id): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT id FROM evaluaciones WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => $id]);

        return (bool) $stmt->fetch();
    }

    public static function cambiarEstado(int $id, string $estado): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("UPDATE evaluaciones SET estado = :estado WHERE id = :id");

        return $stmt->execute([
            ":estado" => $estado,
            ":id"     => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("DELETE FROM evaluaciones WHERE id = :id");

        return $stmt->execute([":id" => $id]);
    }

    /** Inserta las preguntas y opciones de una evaluación. */
    private static function insertarPreguntas(PDO $pdo, int $evaluacionId, array $preguntas): void
    {
        $stmtPregunta = $pdo->prepare("
            INSERT INTO preguntas (evaluacion_id, enunciado, orden)
            VALUES (?, ?, ?)
        ");
        $stmtOpcion = $pdo->prepare("
            INSERT INTO opciones (pregunta_id, texto, es_correcta, orden)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($preguntas as $index => $pregunta) {
            $stmtPregunta->execute([
                $evaluacionId,
                trim($pregunta["enunciado"]),
                $index + 1,
            ]);
            $preguntaId = $pdo->lastInsertId();

            foreach ($pregunta["opciones"] as $opIndex => $opcion) {
                $esCorrecta = (isset($opcion["es_correcta"]) && (int) $opcion["es_correcta"] === 1) ? 1 : 0;
                $stmtOpcion->execute([
                    $preguntaId,
                    trim($opcion["texto"]),
                    $esCorrecta,
                    $opIndex + 1,
                ]);
            }
        }
    }

    /** Crea una evaluación con sus preguntas y opciones en una transacción. */
    public static function crearCompleta(array $data): int
    {
        $pdo = Database::connect();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO evaluaciones (curso_id, titulo, descripcion, porcentaje_aprobacion, estado)
                VALUES (:curso_id, :titulo, :detalle, :porcentaje, :estado)
            ");
            $stmt->execute([
                ":curso_id"   => $data["curso_id"],
                ":titulo"     => $data["titulo"],
                ":detalle"    => $data["descripcion"],
                ":porcentaje" => $data["porcentaje_aprobacion"],
                ":estado"     => $data["estado"] ?? "activo",
            ]);

            $evaluacionId = (int) $pdo->lastInsertId();

            self::insertarPreguntas($pdo, $evaluacionId, $data["preguntas"]);

            $pdo->commit();

            return $evaluacionId;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /** Actualiza la evaluación y reemplaza sus preguntas/opciones. */
    public static function actualizarCompleta(int $id, array $data): void
    {
        $pdo = Database::connect();

        $pdo->beginTransaction();

        try {
            $stmtUpdate = $pdo->prepare("
                UPDATE evaluaciones
                SET curso_id = :curso_id, titulo = :titulo, descripcion = :detalle, porcentaje_aprobacion = :porcentaje
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                ":curso_id"   => $data["curso_id"],
                ":titulo"     => $data["titulo"],
                ":detalle"    => $data["descripcion"],
                ":porcentaje" => $data["porcentaje_aprobacion"],
                ":id"         => $id,
            ]);

            $stmtDelOpciones = $pdo->prepare("
                DELETE FROM opciones
                WHERE pregunta_id IN (SELECT id FROM preguntas WHERE evaluacion_id = :id)
            ");
            $stmtDelOpciones->execute([":id" => $id]);

            $stmtDelete = $pdo->prepare("DELETE FROM preguntas WHERE evaluacion_id = :id");
            $stmtDelete->execute([":id" => $id]);

            self::insertarPreguntas($pdo, $id, $data["preguntas"]);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}

