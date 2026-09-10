<?php header('Content-Type: application/javascript; charset=UTF-8'); ?>

const contentContainer = document.getElementById("admin-content");
const navigationItems = document.querySelectorAll("[data-view]");

const viewScripts = {
    usuarios: "../scripts/usuarios.php",
    courses_admin: "../scripts/courses_admin.php",
    evaluaciones: "../scripts/evaluaciones.php"
};

const viewFiles = {
    dashboard: "dashboard.php",
    usuarios: "usuarios.php",
    empresas: "empresas.php",
    courses_admin: "courses_admin.php",
    evaluaciones: "evaluaciones.php",
    asignaciones: "asignaciones.php",
    resultados: "resultados.php",
    reportes: "reportes.php"
};

function mostrarEstado(mensaje) {
    contentContainer.innerHTML = `
        <div class="dashboard-panel">
            <p>${mensaje}</p>
        </div>
    `;
}

function cargarEstilos(doc, vista) {
    doc.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
        const href = link.getAttribute("href");

        if (!href || href.startsWith("http") || document.querySelector(`link[data-view-css="${href}"]`)) {
            return;
        }

        const stylesheet = document.createElement("link");
        stylesheet.rel = "stylesheet";
        stylesheet.href = href;
        stylesheet.dataset.viewCss = href;
        stylesheet.dataset.view = vista;
        document.head.appendChild(stylesheet);
    });
}

function cargarScript(vista) {
    const scriptSource = viewScripts[vista];

    if (!scriptSource) {
        return;
    }

    const script = document.createElement("script");
    script.src = `${scriptSource}?view=${Date.now()}`;
    script.dataset.viewScript = vista;
    document.body.appendChild(script);
}

async function cargarVista(vista) {
    const viewFile = viewFiles[vista] || viewFiles.dashboard;
    const vistaActual = viewFiles[vista] ? vista : "dashboard";

    navigationItems.forEach(item => {
        item.classList.toggle("active", item.dataset.view === vistaActual);
    });

    contentContainer.innerHTML = '<div class="dashboard-panel"><p>Cargando contenido...</p></div>';

    try {
        const response = await fetch(viewFile, {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        });

        if (!response.ok) {
            throw new Error(`No se pudo cargar ${viewFile}.`);
        }

        const html = await response.text();
        const documentView = new DOMParser().parseFromString(html, "text/html");

        cargarEstilos(documentView, vistaActual);
        contentContainer.innerHTML = documentView.body
            ? documentView.body.innerHTML
            : html;

        contentContainer.querySelectorAll("script").forEach(script => script.remove());
        cargarScript(vistaActual);
    } catch (error) {
        console.error("Error al cargar la vista:", error);
        mostrarEstado("No se pudo cargar el contenido solicitado.");
    }
}

navigationItems.forEach(item => {
    item.addEventListener("click", event => {
        event.preventDefault();
        const vista = item.dataset.view;
        window.location.hash = vista;
        cargarVista(vista);
    });
});

window.addEventListener("hashchange", () => {
    cargarVista(window.location.hash.replace("#", "") || "dashboard");
});

const logoutButton = document.getElementById("logoutButton");

if (logoutButton) {
    logoutButton.addEventListener("click", async event => {
        event.preventDefault();

        try {
            await fetch("../../backend/Api/logout.php", { method: "POST" });
        } finally {
            localStorage.removeItem("sah_user");
            sessionStorage.removeItem("sah_user");
            window.location.href = "../authentication/login.php";
        }
    });
}

cargarVista(window.location.hash.replace("#", "") || "dashboard");
