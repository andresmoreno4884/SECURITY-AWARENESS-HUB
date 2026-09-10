<?php require_once __DIR__ . '/../includes/page_context.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Recuperar contraseña | Security Awareness</title>

    <link rel="stylesheet" href="css/forgot-password.css">

</head>

<body>



    <div class="background"></div>




    <main class="recovery-container">

        <section class="recovery-card">




            <div class="logo">

                <span>SECURITY</span>

                <strong>AWARENESS</strong>

            </div>




            <div class="recovery-header">

                <p class="tag">
                    SECURITY AWARENESS
                </p>

                <h1>
                    Recupera tu
                    <span>contraseña</span>
                </h1>

                <p>
                    Ingresa el correo electrónico con el que
                    registraste tu cuenta.
                </p>

            </div>




            <form id="recoveryForm">




                <div class="input-group">

                    <label for="email">
                        Correo electrónico
                    </label>

                    <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required>

                </div>




                <div id="message"></div>




                <button type="submit" class="recovery-button">
                    Buscar cuenta
                </button>

            </form>




            <form id="newPasswordForm" class="hidden">

                <div class="input-group">

                    <label for="newPassword">
                        Nueva contraseña
                    </label>

                    <input type="password" id="newPassword" placeholder="Nueva contraseña" minlength="6" required>

                </div>


                <div class="input-group">

                    <label for="confirmNewPassword">
                        Confirmar contraseña
                    </label>

                    <input type="password" id="confirmNewPassword" placeholder="Repite tu contraseña" minlength="6"
                        required>

                </div>


                <div id="passwordMessage"></div>


                <button type="submit" class="recovery-button">
                    Cambiar contraseña
                </button>

            </form>




            <div class="login-link">

                <a href="
                login.php">
                    ← Volver al inicio de sesión
                </a>

            </div>




            <a href="../../Landing_Page/index.php" class="back-link">
                Volver al inicio
            </a>


        </section>

    </main>




    <script src="../scripts/forgot-password.php"></script>

</body>

</html>
