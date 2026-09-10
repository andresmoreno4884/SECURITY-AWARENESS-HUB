<?php header('Content-Type: application/javascript; charset=UTF-8'); ?>



const recoveryForm =
    document.getElementById("recoveryForm");

const newPasswordForm =
    document.getElementById("newPasswordForm");

const message =
    document.getElementById("message");

const passwordMessage =
    document.getElementById("passwordMessage");


let userFound = null;




recoveryForm.addEventListener("submit", function (event) {

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




    const users =
        JSON.parse(
            localStorage.getItem("sah_users") || "[]"
        );




    userFound =
        users.find(
            user => user.email === email
        );




    if (!userFound) {

        showMessage(
            message,
            "No encontramos una cuenta con este correo.",
            "error"
        );

        return;
    }




    showMessage(
        message,
        "Cuenta encontrada. Ahora crea una nueva contraseña.",
        "success"
    );




    recoveryForm.classList.add("hidden");




    newPasswordForm.classList.remove("hidden");

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




        if (newPassword.length < 6) {

            showMessage(
                passwordMessage,
                "La contraseña debe tener mínimo 6 caracteres.",
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




        const users =
            JSON.parse(
                localStorage.getItem("sah_users") || "[]"
            );




        const userIndex =
            users.findIndex(
                user =>
                    user.email === userFound.email
            );


        if (userIndex === -1) {

            showMessage(
                passwordMessage,
                "No fue posible actualizar la cuenta.",
                "error"
            );

            return;
        }


        users[userIndex].password =
            newPassword;




        localStorage.setItem(
            "sah_users",
            JSON.stringify(users)
        );




        showMessage(
            passwordMessage,
            "Contraseña actualizada correctamente.",
            "success"
        );




        setTimeout(() => {

            window.location.href = "login.php";

        }, 1500);

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
