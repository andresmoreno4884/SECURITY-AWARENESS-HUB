<p align="center">
  <img src="frontend/Landing_Page/assets/imagenes/Page_Readme.png" alt="Security Awareness Hub" width="1000">
</p>

## Descripción

**Security Awareness Hub** es una plataforma web orientada a organizaciones que buscan fortalecer la cultura de ciberseguridad de sus colaboradores.

La plataforma nace como respuesta a la necesidad de mejorar la preparación de los empleados frente a amenazas digitales como **phishing, ingeniería social, malware, robo de credenciales y malas prácticas en el manejo de información**.

El proyecto busca centralizar los procesos de capacitación y concientización en ciberseguridad dentro de una organización, permitiendo gestionar usuarios, roles y contenidos educativos desde un mismo entorno.

Security Awareness Hub está diseñado para representar una solución aplicable a un entorno empresarial real, donde los administradores puedan gestionar la plataforma y los colaboradores puedan acceder a procesos de formación que les permitan identificar, prevenir y reportar posibles amenazas de seguridad.

El estado actual incluye:

- Inicio de sesión y cierre de sesión con PHP, MySQL y sesiones.
- Recuperación de contraseña conectada a MySQL.
- Gestión administrativa de usuarios, roles y estados.
- Gestión CRUD de empresas.
- Gestión CRUD de cursos.
- Gestión de evaluaciones, preguntas y opciones.
- Gestión de asignaciones de cursos.
- Consulta de resultados y filtros administrativos.
- Generación de reportes de usuarios, cursos, evaluaciones y resultados.
- Interfaz de usuario para consultar cursos asignados.

## Tecnologías

- PHP 8+
- MySQL
- PDO
- JSON y APIs HTTP
- HTML generado mediante vistas PHP
- CSS separado por módulo
- JavaScript servido desde `frontend/scripts/*.php`
- XAMPP con Apache

## Ejecución local

1. Copiar el proyecto dentro de `C:\xampp\htdocs\Security\`.
2. Iniciar Apache y MySQL desde XAMPP.
3. Importar `database/security_awareness_hub.sql` en MySQL.
4. Revisar las credenciales en `backend/Config/database.php`.
5. Abrir `http://localhost/Security/Security-Awareness-Hub_SAH/frontend/Landing_Page/index.php`.

La conexión puede comprobarse en `http://localhost/Security/Security-Awareness-Hub_SAH/backend/Api/test.php`.

## Estructura principal

- `backend/Api/`: endpoints PHP que responden JSON.
- `backend/Config/`: conexión PDO a MySQL.
- `database/`: script de creación de tablas.
- `frontend/authentication/`: login y recuperación de contraseña.
- `frontend/Interface_Administrador/`: vistas del panel administrativo.
- `frontend/Interface_User/`: interfaz del usuario y cursos.
- `frontend/scripts/`: scripts JavaScript servidos como PHP.
- `frontend/includes/`: contexto PHP compartido por las vistas.

No se utilizan archivos `.html`, `.js` ni carpetas `js` en el frontend actual.

## Credenciales de prueba

- Correo: `admin@sah.com`
- Contraseña: `AdminSAH2026!`
- Rol: administrador

Después de iniciar sesión, el administrador puede crear usuarios normales,
empresas, cursos y asignaciones desde el panel.

> Capacita · Concientiza · Protege
