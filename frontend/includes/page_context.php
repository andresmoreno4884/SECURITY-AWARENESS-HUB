<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitles = [
    'index.php' => 'Security Awareness Hub',
    'login.php' => 'Iniciar sesión | Security Awareness Hub',
    'register.php' => 'Crear cuenta | Security Awareness Hub',
    'forgot-password.php' => 'Recuperar contraseña | Security Awareness Hub',
    'course.php' => 'Curso | Security Awareness Hub',
    'panel.php' => 'Panel | Security Awareness Hub',
    'dashboard.php' => 'Dashboard | Security Awareness Hub',
    'usuarios.php' => 'Usuarios | Security Awareness Hub',
    'empresas.php' => 'Empresas | Security Awareness Hub',
    'asignaciones.php' => 'Asignaciones | Security Awareness Hub',
    'courses_admin.php' => 'Cursos | Security Awareness Hub',
    'evaluaciones.php' => 'Evaluaciones | Security Awareness Hub',
    'resultados.php' => 'Resultados | Security Awareness Hub',
    'reportes.php' => 'Reportes | Security Awareness Hub',
];

$pageKey = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$pageTitle = $pageTitles[$pageKey] ?? 'Security Awareness Hub';
$currentYear = (int) date('Y');
$userRole = $_SESSION['user']['rol'] ?? null;

function page_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
