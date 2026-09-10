<?php header('Content-Type: application/javascript; charset=UTF-8'); ?>



const recoveryForm =
    document.getElementById("recoveryForm");

const newPasswordForm =
    document.getElementById("newPasswordForm");

const message =
    document.getElementById("message");

const passwordMessage =
    document.getElementById("passwordMessage");


const API_URL = "../../backend/Api/recover_password.php";

recoveryForm.addEventListener("submit", async function (event) {

    event.preventDefault();


    const email =
        document
            .getElementById("email")
            .value
            .trim()
            .toLowerCase();


    if (!email) {

        showMessage(
            message,
            "Ingresa tu correo electrónico.",
            "error"
        );

        return;
    }




    try {
        const response = await fetch(API_URL, {
            method: "POST",
            credentials: "same-origin",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "verify", correo: email })
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || "No encontramos una cuenta con este correo.");
        }

        showMessage(message, data.message, "success");
        recoveryForm.classList.add("hidden");
        newPasswordForm.classList.remove("hidden");
    } catch (error) {
        showMessage(message, error.message, "error");
    }

});




newPasswordForm.addEventListener(
    "submit",
    function (event) {

        event.preventDefault();


        const newPassword =
            document
                .getElementById("newPassword")
                .value;


        const confirmNewPassword =
            document
                .getElementById("confirmNewPassword")
                .value;




        if (newPassword.length < 8) {

            showMessage(
                passwordMessage,
                "La contraseña debe tener mínimo 8 caracteres.",
                "error"
            );

            return;
        }




        if (newPassword !== confirmNewPassword) {

            showMessage(
                passwordMessage,
                "Las contraseñas no coinciden.",
                "error"
            );

            return;
        }




        try {
            const response = await fetch(API_URL, {
                method: "POST",
                credentials: "same-origin",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ action: "reset", password: newPassword })
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || "No fue posible actualizar la cuenta.");
            }

            showMessage(passwordMessage, data.message, "success");
            setTimeout(() => { window.location.href = "login.php"; }, 1500);
        } catch (error) {
            showMessage(passwordMessage, error.message, "error");
        }

    }
);




function showMessage(element, text, type) {

    element.textContent = text;


    if (type === "error") {

        element.style.color = "#ff6b6b";

    } else {

        element.style.color = "#43e27c";

    }
}
