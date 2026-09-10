<?php header('Content-Type: application/javascript; charset=UTF-8'); ?>






const API_BASE_URL = "http://localhost:3000/api";




document.addEventListener("DOMContentLoaded", function () {

    console.log("Security Awareness Hub - Landing Page cargada.");

    inicializarNavegacion();

    inicializarScroll();

    inicializarAnimaciones();

    inicializarBotones();

});




function inicializarNavegacion() {



    const navLinks = document.querySelectorAll(
        'a[href^="#"]'
    );

    navLinks.forEach(function (link) {

        link.addEventListener("click", function (event) {

            const targetId =
                link.getAttribute("href");

            if (
                !targetId ||
                targetId === "#"
            ) {
                return;
            }

            const target =
                document.querySelector(targetId);

            if (!target) {
                return;
            }

            event.preventDefault();

            target.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });

        });

    });

}




function inicializarScroll() {



    window.addEventListener("scroll", function () {

        const header =
            document.querySelector("header");

        if (!header) {
            return;
        }

        if (window.scrollY > 50) {

            header.classList.add("scrolled");

        } else {

            header.classList.remove("scrolled");

        }

    });

}




function inicializarAnimaciones() {



    const elements =
        document.querySelectorAll(".reveal");

    if (!elements.length) {
        return;
    }




    const observer =
        new IntersectionObserver(

            function (entries) {

                entries.forEach(function (entry) {

                    if (entry.isIntersecting) {

                        entry.target.classList.add(
                            "visible"
                        );

                        observer.unobserve(
                            entry.target
                        );

                    }

                });

            },

            {
                threshold: 0.15
            }

        );


    elements.forEach(function (element) {

        observer.observe(element);

    });

}




function inicializarBotones() {



    const loginButtons =
        document.querySelectorAll(
            '[data-action="login"]'
        );

    const registerButtons =
        document.querySelectorAll(
            '[data-action="register"]'
        );




    loginButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                window.location.href =
                    "../authentication/login.php";

            }
        );

    });




    registerButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                window.location.href =
                    "../authentication/register.php";

            }
        );

    });

}




function obtenerSesion() {



    let session =
        localStorage.getItem("sah_user");




    if (!session) {

        session =
            sessionStorage.getItem("sah_user");

    }




    if (!session) {
        return null;
    }




    try {

        return JSON.parse(session);

    } catch (error) {

        console.error(
            "Error al leer la sesión:",
            error
        );

        return null;

    }

}




function usuarioEstaAutenticado() {

    const session =
        obtenerSesion();

    return session !== null;

}




function cerrarSesion() {



    localStorage.removeItem(
        "sah_user"
    );

    sessionStorage.removeItem(
        "sah_user"
    );




    window.location.href =
        "index.php";

}




function irAlLogin() {

    window.location.href =
        "../authentication/login.php";

}




function irAlRegistro() {

    window.location.href =
        "../authentication/register.php";

}




async function comprobarAPI() {

    try {

        const response =
            await fetch(
                `${API_BASE_URL}`,
                {
                    method: "GET"
                }
            );

        if (response.ok) {

            console.log(
                "API disponible."
            );

            return true;

        }

        console.warn(
            "La API respondió con un error."
        );

        return false;

    } catch (error) {

        console.warn(
            "La API todavía no está disponible."
        );

        return false;

    }

}




function actualizarEstadoSesion() {

    const session =
        obtenerSesion();




    const loginButtons =
        document.querySelectorAll(
            '[data-action="login"]'
        );

    const registerButtons =
        document.querySelectorAll(
            '[data-action="register"]'
        );

    const dashboardButtons =
        document.querySelectorAll(
            '[data-action="dashboard"]'
        );

    const logoutButtons =
        document.querySelectorAll(
            '[data-action="logout"]'
        );




    if (session) {

        dashboardButtons.forEach(
            function (button) {

                button.style.display =
                    "";

            }
        );

        logoutButtons.forEach(
            function (button) {

                button.style.display =
                    "";

            }
        );



        loginButtons.forEach(
            function (button) {

                button.style.display =
                    "none";

            }
        );

        registerButtons.forEach(
            function (button) {

                button.style.display =
                    "none";

            }
        );

    } else {



        dashboardButtons.forEach(
            function (button) {

                button.style.display =
                    "none";

            }
        );

        logoutButtons.forEach(
            function (button) {

                button.style.display =
                    "none";

            }
        );

    }

}




document.addEventListener(
    "click",
    function (event) {

        const button =
            event.target.closest(
                '[data-action="dashboard"]'
            );

        if (!button) {
            return;
        }

        const session =
            obtenerSesion();

        if (!session) {

            event.preventDefault();

            irAlLogin();

            return;

        }



    }
);




document.addEventListener(
    "click",
    function (event) {

        const button =
            event.target.closest(
                '[data-action="logout"]'
            );

        if (!button) {
            return;
        }

        event.preventDefault();

        cerrarSesion();

    }
);




document.addEventListener(
    "DOMContentLoaded",
    function () {

        actualizarEstadoSesion();

    }
);




window.irAlLogin =
    irAlLogin;

window.irAlRegistro =
    irAlRegistro;

window.cerrarSesion =
    cerrarSesion;

window.obtenerSesion =
    obtenerSesion;

window.usuarioEstaAutenticado =
    usuarioEstaAutenticado;

window.comprobarAPI =
    comprobarAPI;
