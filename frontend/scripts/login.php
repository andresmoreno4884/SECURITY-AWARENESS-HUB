<?php header('Content-Type: application/javascript; charset=UTF-8'); ?>






const loginForm =
    document.getElementById("loginForm");

const emailInput =
    document.getElementById("email");

const passwordInput =
    document.getElementById("password");

const rememberInput =
    document.getElementById("remember");




let loginMessage =
    document.getElementById("loginMessage");

if (!loginMessage && loginForm) {

    loginMessage =
        document.createElement("div");

    loginMessage.id =
        "loginMessage";

    loginMessage.className =
        "login-message";

    const loginButton =
        loginForm.querySelector(".login-button");

    if (loginButton) {

        loginForm.insertBefore(
            loginMessage,
            loginButton
        );

    } else {

        loginForm.appendChild(
            loginMessage
        );

    }

}




function showMessage(message, type) {

    if (!loginMessage) {
        return;
    }

    loginMessage.textContent =
        message;

    loginMessage.className =
        "login-message " + type;

}


function clearMessage() {

    if (!loginMessage) {
        return;
    }

    loginMessage.textContent =
        "";

    loginMessage.className =
        "login-message";

}




function validEmail(email) {

    const emailRegex =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    return emailRegex.test(email);

}




if (loginForm) {

    loginForm.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();

            clearMessage();




            const email =
                emailInput.value
                    .trim()
                    .toLowerCase();

            const password =
                passwordInput.value;




            if (!email || !password) {

                showMessage(
                    "Por favor completa todos los campos.",
                    "error"
                );

                return;

            }


            if (!validEmail(email)) {

                showMessage(
                    "Ingresa un correo electrónico válido.",
                    "error"
                );

                emailInput.focus();

                return;

            }


            if (password.length < 6) {

                showMessage(
                    "La contraseña debe tener mínimo 6 caracteres.",
                    "error"
                );

                passwordInput.focus();

                return;

            }




            const loginButton =
                loginForm.querySelector(".login-button");

            if (loginButton) {

                loginButton.disabled =
                    true;

                loginButton.textContent =
                    "Iniciando sesión...";

            }


            try {




                const response =
                    await fetch(
                        "../../backend/Api/login.php",
                        {
                            method: "POST",

                            headers: {
                                "Content-Type":
                                    "application/json"
                            },

                            credentials: "include",

                            body:
                                JSON.stringify({
                                    email: email,
                                    password: password
                                })
                        }
                    );




                const responseText =
                    await response.text();

                let data;

                try {

                    data =
                        JSON.parse(
                            responseText
                        );

                } catch (jsonError) {

                    console.error(
                        "PHP no devolvió JSON válido:",
                        responseText
                    );

                    throw new Error(
                        "El servidor no devolvió una respuesta JSON válida."
                    );

                }




                if (
                    !response.ok ||
                    !data.success
                ) {

                    showMessage(
                        data.message ||
                        "No fue posible iniciar sesión.",
                        "error"
                    );

                    if (loginButton) {

                        loginButton.disabled =
                            false;

                        loginButton.textContent =
                            "Iniciar sesión";

                    }

                    return;

                }




                const user =
                    data.user;




                if (!user || !user.rol) {

                    console.error(
                        "Respuesta de login sin información de usuario:",
                        data
                    );

                    throw new Error(
                        "El servidor no devolvió correctamente la información del usuario."
                    );

                }




                const sessionUser = {

                    id:
                        user.id,

                    nombres:
                        user.nombres,

                    apellidos:
                        user.apellidos,

                    email:
                        user.correo,

                    role:
                        user.rol,

                    loginDate:
                        new Date().toISOString()

                };


                const sessionData =
                    JSON.stringify(
                        sessionUser
                    );




                if (
                    rememberInput &&
                    rememberInput.checked
                ) {

                    localStorage.setItem(
                        "sah_user",
                        sessionData
                    );

                    sessionStorage.removeItem(
                        "sah_user"
                    );

                } else {

                    sessionStorage.setItem(
                        "sah_user",
                        sessionData
                    );

                    localStorage.removeItem(
                        "sah_user"
                    );

                }




                showMessage(
                    "Inicio de sesión correcto. Entrando...",
                    "success"
                );




                setTimeout(
                    function () {




                        if (
                            user.rol === "admin"
                        ) {

                            window.location.href =
                                "../Interface_Administrador/panel.php";

                            return;

                        }




                        window.location.href =
                            "../Interface_User/Curse/course.php";


                    },
                    700
                );


            } catch (error) {

                console.error(
                    "Error al conectar con login.php:",
                    error
                );


                showMessage(
                    error.message ||
                    "No se pudo conectar con el servidor.",
                    "error"
                );


                if (loginButton) {

                    loginButton.disabled =
                        false;

                    loginButton.textContent =
                        "Iniciar sesión";

                }

            }

        }
    );

}
