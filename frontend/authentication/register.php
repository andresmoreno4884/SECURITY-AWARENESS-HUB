<?php require_once __DIR__ . '/../includes/page_context.php'; ?>
<?php $publicRegistrationEnabled = false; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= page_escape($pageTitle) ?></title>

    <link rel="stylesheet" href="css/register.css">
</head>

<body>


    <div class="background"></div>


    <main class="register-container">

        <section class="register-card">


            <div class="logo">
                <span>SECURITY</span>
                <strong>AWARENESS</strong>
            </div>


            <div class="register-header">

                <p class="tag">SECURITY AWARENESS</p>

                <h1>
                    Registro
                    <span>controlado</span>
                </h1>

                <p>
                    Las cuentas son creadas por el administrador
                    desde el dashboard de usuarios.
                </p>

            </div>

            <?php if ($publicRegistrationEnabled): ?>


            <form id="registerForm">


                <div class="input-group">

                    <label for="nombres">
                        Nombres
                    </label>

                    <input
                        type="text"
                        id="nombres"
                        name="nombres"
                        placeholder="Ingresa tus nombres"
                        autocomplete="given-name"
                        required
                    >

                </div>


                <div class="input-group">

                    <label for="apellidos">
                        Apellidos
                    </label>

                    <input
                        type="text"
                        id="apellidos"
                        name="apellidos"
                        placeholder="Ingresa tus apellidos"
                        autocomplete="family-name"
                        required
                    >

                </div>


                <div class="input-group">

                    <label for="correo">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="ejemplo@correo.com"
                        autocomplete="email"
                        required
                    >

                </div>


                <div class="input-group">

                    <label for="password">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Crea una contraseña"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                </div>


                <div class="input-group">

                    <label for="confirmPassword">
                        Confirmar contraseña
                    </label>

                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirmPassword"
                        placeholder="Repite tu contraseña"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                </div>


                <div id="message"></div>


                <button
                    type="submit"
                    class="register-button"
                >
                    Crear cuenta
                </button>

            </form>

            <script src="../scripts/register.php"></script>

            <?php else: ?>

            <div class="registration-notice" role="status">
                <p>
                    El registro público está desactivado.
                </p>
                <p>
                    Solicita al administrador que cree tu cuenta y te asigne el acceso correspondiente.
                </p>
            </div>

            <?php endif; ?>


            <div class="login-link">

                <span>
                    ¿Ya tienes una cuenta?
                </span>

                <a href="login.php">
                    Iniciar sesión
                </a>

            </div>


            <a
                href="../Landing_Page/index.php"
                class="back-link"
            >
                ← Volver al inicio
            </a>

        </section>

    </main>

</body>

</html>
