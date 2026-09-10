<?php require_once __DIR__ . '/../includes/page_context.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Security Awareness Hub - Iniciar sesión</title>

    <link rel="stylesheet" href="css/login.css">
</head>

<body>



    <div class="login-background"></div>




    <main class="login-container">

        <section class="login-card">

            <div class="login-logo">
                <span>SECURITY</span>
                <strong>AWARENESS</strong>
            </div>

            <h1>Bienvenido</h1>

            <p class="login-description">
                Inicia sesión para continuar con tu capacitación
                en seguridad digital.
            </p>

            <form id="loginForm">

                <div class="form-group">

                    <label for="email">
                        Correo electrónico
                    </label>

                    <input type="email" id="email" name="email" placeholder="Ingresa tu correo" required>

                </div>


                <div class="form-group">

                    <label for="password">
                        Contraseña
                    </label>

                    <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>

                </div>


                <div class="login-options">

                    <label class="remember">

                        <input type="checkbox" id="remember">

                        <span>Recordarme</span>

                    </label>


                    <a href="/frontend/authentication/forgot-password.php" class="forgot-password">
                        ¿Olvidaste tu contraseña?
                    </a>

                </div>


                <button type="submit" class="login-button">
                    Iniciar sesión
                </button>

            </form>


            <p class="register-text">

                ¿No tienes una cuenta?

                <span>
                    Contacta al administrador.
                </span>

            </p>

        </section>

    </main>




    <script src="../scripts/login.php"></script>

</body>

</html>
