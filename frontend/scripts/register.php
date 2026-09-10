<?php header('Content-Type: application/javascript; charset=UTF-8'); ?>



const registerForm = document.getElementById("registerForm");
const message = document.getElementById("message");

registerForm.addEventListener("submit", async function (event) {

    event.preventDefault();



    const nombres = document
        .getElementById("nombres")
        .value
        .trim();

    const apellidos = document
        .getElementById("apellidos")
        .value
        .trim();

    const correo = document
        .getElementById("correo")
        .value
        .trim()
        .toLowerCase();

    const password = document
        .getElementById("password")
        .value;

    const confirmPassword = document
        .getElementById("confirmPassword")
        .value;



    message.textContent = "";
    message.className = "";



    if (
        !nombres ||
        !apellidos ||
        !correo ||
        !password ||
        !confirmPassword
    ) {

        showMessage(
            "Completa todos los campos.",
            "error"
        );

        return;
    }



    if (nombres.length < 2) {

        showMessage(
            "Los nombres deben tener al menos 2 caracteres.",
            "error"
        );

        return;
    }



    if (apellidos.length < 2) {

        showMessage(
            "Los apellidos deben tener al menos 2 caracteres.",
            "error"
        );

        return;
    }



    const emailRegex =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(correo)) {

        showMessage(
            "Ingresa un correo electrónico válido.",
            "error"
        );

        return;
    }



    if (password.length < 8) {

        showMessage(
            "La contraseña debe tener mínimo 8 caracteres.",
            "error"
        );

        return;
    }



    if (password !== confirmPassword) {

        showMessage(
            "Las contraseñas no coinciden.",
            "error"
        );

        return;
    }



    const registerButton =
        registerForm.querySelector(".register-button");

    registerButton.disabled = true;

    registerButton.textContent =
        "Creando cuenta...";



    const userData = {

        nombres: nombres,

        apellidos: apellidos,

        correo: correo,

        password: password

    };

    try {



        const response = await fetch(
            "../../backend/Api/register.php",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify(userData)
            }
        );



        const data = await response.json();



        if (!response.ok || !data.success) {

            showMessage(
                data.message ||
                "No fue posible crear la cuenta.",
                "error"
            );

            registerButton.disabled = false;

            registerButton.textContent =
                "Crear cuenta";

            return;
        }



        showMessage(
            data.message ||
            "Usuario registrado correctamente.",
            "success"
        );



        setTimeout(() => {

            window.location.href =
                "login.php";

        }, 1500);

    } catch (error) {

        console.error(
            "Error al conectar con el servidor:",
            error
        );

        showMessage(
            "No se pudo conectar con el servidor.",
            "error"
        );

        registerButton.disabled = false;

        registerButton.textContent =
            "Crear cuenta";
    }

});




function showMessage(text, type) {

    message.textContent = text;

    if (type === "error") {

        message.style.color = "#ff6b6b";

    } else {

        message.style.color = "#43e27c";
    }
}
