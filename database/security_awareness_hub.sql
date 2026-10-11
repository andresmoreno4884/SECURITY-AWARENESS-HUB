/* ============================================================
   SECURITY AWARENESS HUB
   BASE DE DATOS PRINCIPAL
   SQL MAESTRO OFICIAL
   ============================================================

   IMPORTANTE:
   - Este script NO elimina la base de datos.
   - Este script NO elimina tablas.
   - Este script NO elimina usuarios.
   - Este script NO sobrescribe contraseñas existentes.
   - Puede ejecutarse nuevamente sin destruir información.

   ============================================================ */


/* ============================================================
   1. CREAR BASE DE DATOS
   ============================================================ */

CREATE DATABASE IF NOT EXISTS security_awareness_hub
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE security_awareness_hub;


/* ============================================================
   2. TABLA: empresas
   ============================================================ */

CREATE TABLE IF NOT EXISTS empresas (

    id INT(11) NOT NULL AUTO_INCREMENT,

    nombre VARCHAR(150) NOT NULL,

    nit VARCHAR(30) NOT NULL,

    correo VARCHAR(150) NOT NULL,

    telefono VARCHAR(30) DEFAULT NULL,

    direccion VARCHAR(200) DEFAULT NULL,

    tipo ENUM(
        'privada',
        'publica',
        'mixta'
    ) NOT NULL DEFAULT 'privada',

    estado ENUM(
        'activo',
        'inactivo'
    ) NOT NULL DEFAULT 'activo',

    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY unique_nit (nit),

    KEY idx_empresa_estado (estado),

    KEY idx_empresa_nombre (nombre)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


/* ============================================================
   3. TABLA: usuarios
   ============================================================ */

CREATE TABLE IF NOT EXISTS usuarios (

    id INT(11) NOT NULL AUTO_INCREMENT,

    nombres VARCHAR(100) NOT NULL,

    apellidos VARCHAR(100) NOT NULL,

    correo VARCHAR(150) NOT NULL,

    password VARCHAR(255) NOT NULL,

    rol ENUM(
        'admin',
        'admin_empresa',
        'empleado'
    ) NOT NULL DEFAULT 'empleado',

    empresa_id INT(11) DEFAULT NULL,

    estado ENUM(
        'activo',
        'inactivo'
    ) NOT NULL DEFAULT 'activo',

    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY unique_correo (correo),

    KEY idx_usuario_empresa (empresa_id),

    KEY idx_usuario_rol (rol),

    KEY idx_usuario_estado (estado),

    KEY idx_usuario_nombre (nombres),

    KEY idx_usuario_apellidos (apellidos)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


/* ============================================================
   4. TABLA: cursos
   ============================================================ */

CREATE TABLE IF NOT EXISTS cursos (

    id INT(11) NOT NULL AUTO_INCREMENT,

    titulo VARCHAR(150) NOT NULL,

    descripcion TEXT DEFAULT NULL,

    nivel_dificultad ENUM(
        'basico',
        'intermedio',
        'avanzado'
    ) NOT NULL DEFAULT 'basico',

    duracion INT(11) NOT NULL DEFAULT 0,

    numero_modulos INT(11) NOT NULL DEFAULT 1,

    porcentaje_aprobacion INT(11) NOT NULL DEFAULT 70,

    estado ENUM(
        'activo',
        'inactivo'
    ) NOT NULL DEFAULT 'activo',

    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_curso_estado (estado),

    KEY idx_curso_nivel (nivel_dificultad),

    KEY idx_curso_titulo (titulo)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


/* ============================================================
   5. TABLA: evaluaciones
   ============================================================ */

CREATE TABLE IF NOT EXISTS evaluaciones (

    id INT(11) NOT NULL AUTO_INCREMENT,

    curso_id INT(11) NOT NULL,

    titulo VARCHAR(150) NOT NULL,

    descripcion TEXT NOT NULL,

    porcentaje_aprobacion INT(11) NOT NULL DEFAULT 70,

    estado ENUM(
        'activo',
        'inactivo'
    ) NOT NULL DEFAULT 'activo',

    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_evaluacion_curso (curso_id),

    KEY idx_evaluacion_estado (estado),

    KEY idx_evaluacion_titulo (titulo)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


/* ============================================================
   6. TABLA: preguntas
   ============================================================ */

CREATE TABLE IF NOT EXISTS preguntas (

    id INT(11) NOT NULL AUTO_INCREMENT,

    evaluacion_id INT(11) NOT NULL,

    enunciado TEXT NOT NULL,

    orden INT(11) NOT NULL DEFAULT 1,

    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_pregunta_evaluacion (evaluacion_id),

    KEY idx_pregunta_orden (orden)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


/* ============================================================
   7. TABLA: opciones
   ============================================================ */

CREATE TABLE IF NOT EXISTS opciones (

    id INT(11) NOT NULL AUTO_INCREMENT,

    pregunta_id INT(11) NOT NULL,

    texto VARCHAR(255) NOT NULL,

    es_correcta TINYINT(1) NOT NULL DEFAULT 0,

    orden INT(11) NOT NULL DEFAULT 1,

    PRIMARY KEY (id),

    KEY idx_opcion_pregunta (pregunta_id),

    KEY idx_opcion_correcta (es_correcta),

    KEY idx_opcion_orden (orden)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


/* ============================================================
   8. TABLA: asignaciones
   ============================================================

   Relaciona:
   usuario -> curso

   Permite controlar:
   - Cursos asignados
   - Cursos en progreso
   - Cursos completados
   - Cursos cancelados
   ============================================================ */

CREATE TABLE IF NOT EXISTS asignaciones (

    id INT(11) NOT NULL AUTO_INCREMENT,

    usuario_id INT(11) NOT NULL,

    curso_id INT(11) NOT NULL,

    estado ENUM(
        'asignado',
        'en_progreso',
        'completado',
        'cancelado'
    ) NOT NULL DEFAULT 'asignado',

    fecha_asignacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_finalizacion TIMESTAMP NULL DEFAULT NULL,

    PRIMARY KEY (id),

    UNIQUE KEY unique_usuario_curso (
        usuario_id,
        curso_id
    ),

    KEY idx_asignacion_usuario (
        usuario_id
    ),

    KEY idx_asignacion_curso (
        curso_id
    ),

    KEY idx_asignacion_estado (
        estado
    )

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


/* ============================================================
   9. TABLA: progreso
   ============================================================ */

CREATE TABLE IF NOT EXISTS progreso (

    id INT(11) NOT NULL AUTO_INCREMENT,

    asignacion_id INT(11) NOT NULL,

    porcentaje INT(11) NOT NULL DEFAULT 0,

    modulos_completados INT(11) NOT NULL DEFAULT 0,

    ultima_actividad TIMESTAMP NULL DEFAULT NULL,

    fecha_actualizacion TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY unique_asignacion (
        asignacion_id
    ),

    KEY idx_progreso_asignacion (
        asignacion_id
    ),

    KEY idx_progreso_porcentaje (
        porcentaje
    )

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


/* ============================================================
   10. TABLA: resultados_evaluaciones
   ============================================================

   Permite múltiples intentos de una evaluación.

   Por eso NO existe una restricción UNIQUE entre:
   asignacion_id + evaluacion_id
   ============================================================ */

CREATE TABLE IF NOT EXISTS resultados_evaluaciones (

    id INT(11) NOT NULL AUTO_INCREMENT,

    asignacion_id INT(11) NOT NULL,

    evaluacion_id INT(11) NOT NULL,

    puntaje INT(11) NOT NULL DEFAULT 0,

    aprobado TINYINT(1) NOT NULL DEFAULT 0,

    intento INT(11) NOT NULL DEFAULT 1,

    fecha_presentacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_resultado_asignacion (
        asignacion_id
    ),

    KEY idx_resultado_evaluacion (
        evaluacion_id
    ),

    KEY idx_resultado_aprobado (
        aprobado
    ),

    KEY idx_resultado_fecha (
        fecha_presentacion
    )

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


/* ============================================================
   11. TABLA: actividad_acceso
   ============================================================

   Registra:

   - Login
   - Logout
   - Fecha y hora
   - IP

   Esto permitirá posteriormente saber:
   - Quién inició sesión
   - Cuándo inició sesión
   - Quién cerró sesión
   - Actividad de acceso por empresa
   ============================================================ */

CREATE TABLE IF NOT EXISTS actividad_acceso (

    id INT(11) NOT NULL AUTO_INCREMENT,

    usuario_id INT(11) NOT NULL,

    tipo ENUM(
        'login',
        'logout'
    ) NOT NULL,

    fecha_acceso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    ip VARCHAR(45) DEFAULT NULL,

    PRIMARY KEY (id),

    KEY idx_actividad_usuario (
        usuario_id
    ),

    KEY idx_actividad_tipo (
        tipo
    ),

    KEY idx_actividad_fecha (
        fecha_acceso
    )

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


/* ============================================================
   12. CREAR ADMINISTRADOR INICIAL
   ============================================================

   IMPORTANTE:

   El administrador SOLO se crea si NO existe.

   Si admin@sah.com ya existe:
   - NO se modifica.
   - NO se cambia la contraseña.
   - NO se cambia el rol.
   - NO se cambia el estado.

   Esto evita repetir el problema que acabamos de solucionar.

   Contraseña inicial:
   SAH_Admin_2026!

   La contraseña NO se almacena en texto plano.
   Se almacena mediante bcrypt.

   ============================================================ */

INSERT INTO usuarios
(
    nombres,
    apellidos,
    correo,
    password,
    rol,
    empresa_id,
    estado
)
SELECT
    'Administrador',
    'SAH',
    'admin@sah.com',
    '$2y$12$XIet1KrVgTeWtLpbgP1yAOGdUUx3biyWYcUnbt5LLxHWmUQ1AK2j.',
    'admin',
    NULL,
    'activo'
WHERE NOT EXISTS
(
    SELECT 1
    FROM usuarios
    WHERE correo = 'admin@sah.com'
);


/* ============================================================
   13. CONSULTAS DE VERIFICACIÓN
   ============================================================

   Estas consultas NO modifican información.
   Sirven para comprobar que la estructura existe.
   ============================================================ */


/* Ver usuarios */

SELECT
    id,
    nombres,
    apellidos,
    correo,
    rol,
    empresa_id,
    estado,
    fecha_registro
FROM usuarios
ORDER BY id;


/* Ver empresas */

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
ORDER BY id;


/* Ver cursos */

SELECT
    id,
    titulo,
    nivel_dificultad,
    duracion,
    numero_modulos,
    porcentaje_aprobacion,
    estado,
    fecha_creacion
FROM cursos
ORDER BY id;


/* Ver asignaciones */

SELECT
    id,
    usuario_id,
    curso_id,
    estado,
    fecha_asignacion,
    fecha_finalizacion
FROM asignaciones
ORDER BY id;


/* Ver progreso */

SELECT
    id,
    asignacion_id,
    porcentaje,
    modulos_completados,
    ultima_actividad,
    fecha_actualizacion
FROM progreso
ORDER BY id;


/* Ver resultados de evaluaciones */

SELECT
    id,
    asignacion_id,
    evaluacion_id,
    puntaje,
    aprobado,
    intento,
    fecha_presentacion
FROM resultados_evaluaciones
ORDER BY id;


/* Ver actividad de acceso */

SELECT
    id,
    usuario_id,
    tipo,
    fecha_acceso,
    ip
FROM actividad_acceso
ORDER BY id DESC;