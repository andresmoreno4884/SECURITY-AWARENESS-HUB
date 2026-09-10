
DROP DATABASE IF EXISTS security_awareness_hub;

CREATE DATABASE security_awareness_hub
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE security_awareness_hub;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('usuario', 'admin')
        NOT NULL DEFAULT 'usuario',
    estado ENUM('activo', 'inactivo')
        NOT NULL DEFAULT 'activo',
    fecha_registro TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

INSERT INTO usuarios (
    nombres,
    apellidos,
    correo,
    password,
    rol,
    estado
)
VALUES (
    'Administrador',
    'SAH',
    'admin@sah.com',
    '$2y$12$awlzns0sJO9ICU3Y.SfiXe4Ia3PhyHVLMsuOgw4rmfMv3ivoR/lcS',
    'admin',
    'activo'
);

CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,

    titulo VARCHAR(150) NOT NULL,

    descripcion TEXT NOT NULL,

    nivel_dificultad ENUM(
        'basico',
        'intermedio',
        'avanzado'
    ) NOT NULL DEFAULT 'basico',

    duracion INT NOT NULL,

    numero_modulos INT NOT NULL DEFAULT 1,

    porcentaje_aprobacion INT NOT NULL DEFAULT 70,

    estado ENUM(
        'activo',
        'inactivo'
    ) NOT NULL DEFAULT 'activo',

    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


INSERT INTO cursos (
    titulo,
    descripcion,
    nivel_dificultad,
    duracion,
    numero_modulos,
    porcentaje_aprobacion,
    estado
)
VALUES (
    'Fundamentos de Ciberseguridad',
    'Introducción a los conceptos básicos de seguridad digital.',
    'basico',
    5,
    5,
    70,
    'activo'
);


INSERT INTO cursos (
    titulo,
    descripcion,
    nivel_dificultad,
    duracion,
    numero_modulos,
    porcentaje_aprobacion,
    estado
)
VALUES (
    'Seguridad de Contraseñas',
    'Aprende a crear, gestionar y proteger contraseñas seguras para evitar accesos no autorizados.',
    'basico',
    4,
    4,
    70,
    'activo'
);

CREATE TABLE IF NOT EXISTS evaluaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,

    curso_id INT NOT NULL,

    titulo VARCHAR(150) NOT NULL,

    descripcion TEXT NOT NULL,

    porcentaje_aprobacion INT NOT NULL DEFAULT 70,

    estado ENUM(
        'activo',
        'inactivo'
    ) NOT NULL DEFAULT 'activo',

    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_evaluaciones_curso
        FOREIGN KEY (curso_id)
        REFERENCES cursos(id)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,

    evaluacion_id INT NOT NULL,

    enunciado TEXT NOT NULL,

    orden INT NOT NULL DEFAULT 1,

    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_preguntas_evaluacion
        FOREIGN KEY (evaluacion_id)
        REFERENCES evaluaciones(id)
        ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS opciones (
    id INT AUTO_INCREMENT PRIMARY KEY,

    pregunta_id INT NOT NULL,

    texto VARCHAR(255) NOT NULL,

    es_correcta TINYINT(1) NOT NULL DEFAULT 0,

    orden INT NOT NULL DEFAULT 1,

    CONSTRAINT fk_opciones_pregunta
        FOREIGN KEY (pregunta_id)
        REFERENCES preguntas(id)
        ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_evaluaciones_curso ON evaluaciones(curso_id);
CREATE INDEX idx_preguntas_evaluacion ON preguntas(evaluacion_id);
CREATE INDEX idx_opciones_pregunta ON opciones(pregunta_id);

CREATE TABLE IF NOT EXISTS empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(150) NOT NULL,

    nit VARCHAR(30) NOT NULL UNIQUE,

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

    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

ALTER TABLE usuarios
    ADD COLUMN empresa_id INT NULL AFTER apellidos,
    ADD CONSTRAINT fk_usuarios_empresa
        FOREIGN KEY (empresa_id)
        REFERENCES empresas(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

CREATE INDEX idx_usuarios_empresa ON usuarios(empresa_id);

CREATE TABLE IF NOT EXISTS asignaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT NOT NULL,

    curso_id INT NULL,

    evaluacion_id INT NULL,

    tipo ENUM(
        'curso',
        'evaluacion'
    ) NOT NULL DEFAULT 'curso',

    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    estado ENUM(
        'pendiente',
        'progreso',
        'completado'
    ) NOT NULL DEFAULT 'pendiente',

    CONSTRAINT fk_asignaciones_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_asignaciones_curso
        FOREIGN KEY (curso_id)
        REFERENCES cursos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_asignaciones_evaluacion
        FOREIGN KEY (evaluacion_id)
        REFERENCES evaluaciones(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT uq_asignacion
        UNIQUE (
            usuario_id,
            curso_id,
            evaluacion_id
        )

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT NOT NULL,

    evaluacion_id INT NOT NULL,

    puntuacion DECIMAL(5,2) NOT NULL DEFAULT 0,

    porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,

    preguntas_totales INT NOT NULL DEFAULT 0,

    preguntas_correctas INT NOT NULL DEFAULT 0,

    preguntas_incorrectas INT NOT NULL DEFAULT 0,

    aprobado ENUM('si','no') NOT NULL DEFAULT 'no',

    fecha_resultado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_resultados_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_resultados_evaluacion
        FOREIGN KEY (evaluacion_id)
        REFERENCES evaluaciones(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

SELECT
    id, nombres, apellidos, empresa_id, correo, rol, estado, fecha_registro
FROM usuarios;

SHOW TABLES;
