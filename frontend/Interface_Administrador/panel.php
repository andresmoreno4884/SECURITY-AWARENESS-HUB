<?php require_once __DIR__ . '/../includes/page_context.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Panel Administrador | Security Awareness Hub
    </title>


    <link
        rel="stylesheet"
        href="css/Dashboard.css">

</head>

<body>



    <div class="admin-layout">




        <aside class="admin-sidebar">




            <div class="admin-brand">

                <div class="admin-brand-icon">
                    SAH
                </div>

                <div>

                    <strong>
                        Security
                    </strong>

                    <span>
                        Awareness Hub
                    </span>

                </div>

            </div>




            <div class="admin-profile">

                <div class="profile-avatar">
                    A
                </div>

                <div>

                    <strong>
                        Administrador
                    </strong>

                    <span>
                        Panel administrativo
                    </span>

                </div>

            </div>




            <nav class="admin-navigation">

                <span class="navigation-title">
                    PRINCIPAL
                </span>




                <a
                    href="#dashboard"
                    class="nav-item active"
                    data-view="dashboard">

                    <span class="nav-icon">
                        ▦
                    </span>

                    <span>
                        Dashboard
                    </span>

                </a>


                <span class="navigation-title">
                    ADMINISTRACIÓN
                </span>




                <a
                    href="#usuarios"
                    class="nav-item"
                    data-view="usuarios">

                    <span class="nav-icon">
                        ♙
                    </span>

                    <span>
                        Usuarios
                    </span>

                </a>




                <a
                    href="#empresas"
                    class="nav-item"
                    data-view="empresas">

                    <span class="nav-icon">
                        ▤
                    </span>

                    <span>
                        Empresas
                    </span>

                </a>




                <a
                    href="#courses_admin"
                    class="nav-item"
                    data-view="courses_admin">

                    <span class="nav-icon">
                        ▣
                    </span>

                    <span>
                        Cursos
                    </span>

                </a>




                <a
                    href="#evaluaciones"
                    class="nav-item"
                    data-view="evaluaciones">

                    <span class="nav-icon">
                        ✓
                    </span>

                    <span>
                        Evaluaciones
                    </span>

                </a>




                <a
                    href="#asignaciones"
                    class="nav-item"
                    data-view="asignaciones">

                    <span class="nav-icon">
                        ⇄
                    </span>

                    <span>
                        Asignaciones
                    </span>

                </a>




                <a
                    href="#resultados"
                    class="nav-item"
                    data-view="resultados">

                    <span class="nav-icon">
                        %
                    </span>

                    <span>
                        Resultados
                    </span>

                </a>




                <a
                    href="#reportes"
                    class="nav-item"
                    data-view="reportes">

                    <span class="nav-icon">
                        ▤
                    </span>

                    <span>
                        Reportes
                    </span>

                </a>

            </nav>




            <div class="sidebar-footer">

                <button
                    type="button"
                    id="logoutButton"
                    class="logout-button">

                    <span>
                        ⇥
                    </span>

                    <span>
                        Cerrar sesión
                    </span>

                </button>

            </div>

        </aside>




        <main class="admin-main">




            <header class="admin-topbar">

                <div>

                    <span class="topbar-label">
                        ADMINISTRACIÓN
                    </span>

                    <h1>
                        Security Awareness Hub
                    </h1>

                </div>


                <div class="topbar-status">

                    <span class="status-dot"></span>

                    <span>
                        Administrador
                    </span>

                </div>

            </header>




            <section
                id="admin-content"
                class="admin-content">



            </section>


        </main>

    </div>




    <script
        src="../scripts/dashboard.php">
    </script>

</body>

</html>
