<?php
header("Content-Type: application/javascript; charset=UTF-8");
?>

(function () {
    "use strict";

    const API_URL = "../../backend/Api/reportes.php";
    const elementos = {};

    function obtenerElementos() {
        ["reportType", "reportPeriod", "btnGenerarReporte", "reporteTotalUsuarios", "reporteTotalCursos", "reportePromedio", "reportEmptyState", "reportResults", "reportTableHead", "reportTableBody"].forEach(id => {
            elementos[id] = document.getElementById(id);
        });
    }

    function escapar(valor) {
        const div = document.createElement("div");
        div.textContent = valor ?? "";
        return div.innerHTML;
    }

    async function generarReporte() {
        const tipo = elementos.reportType.value;
        if (!tipo) {
            elementos.reportEmptyState.hidden = false;
            elementos.reportResults.hidden = true;
            return;
        }

        elementos.btnGenerarReporte.disabled = true;
        elementos.btnGenerarReporte.textContent = "Generando...";

        try {
            const params = new URLSearchParams({
                tipo,
                periodo: elementos.reportPeriod.value
            });
            const respuesta = await fetch(`${API_URL}?${params}`, {
                credentials: "same-origin",
                headers: { "Accept": "application/json" }
            });
            const datos = await respuesta.json();
            if (!respuesta.ok || !datos.success) {
                throw new Error(datos.message || "No fue posible generar el reporte.");
            }

            elementos.reporteTotalUsuarios.textContent = datos.estadisticas.usuarios;
            elementos.reporteTotalCursos.textContent = datos.estadisticas.cursos;
            elementos.reportePromedio.textContent = `${Number(datos.estadisticas.promedio).toFixed(2)}%`;
            elementos.reportTableHead.innerHTML = `<tr>${Object.values(datos.columnas).map(columna => `<th>${escapar(columna)}</th>`).join("")}</tr>`;
            elementos.reportTableBody.innerHTML = datos.filas.length
                ? datos.filas.map(fila => `<tr>${Object.keys(datos.columnas).map(columna => `<td>${escapar(formatear(fila[columna], columna))}</td>`).join("")}</tr>`).join("")
                : `<tr><td colspan="${Object.keys(datos.columnas).length}">No hay datos para este reporte.</td></tr>`;
            elementos.reportEmptyState.hidden = true;
            elementos.reportResults.hidden = false;
        } catch (error) {
            elementos.reportEmptyState.hidden = false;
            elementos.reportResults.hidden = true;
            elementos.reportEmptyState.querySelector("p").textContent = error.message;
        } finally {
            elementos.btnGenerarReporte.disabled = false;
            elementos.btnGenerarReporte.textContent = "Generar reporte";
        }
    }

    function formatear(valor, columna) {
        if (valor === null || valor === undefined || valor === "") return "—";
        if (columna === "porcentaje") return `${Number(valor).toFixed(2)}%`;
        if (columna === "aprobado") return valor === "si" ? "Aprobado" : "Reprobado";
        return valor;
    }

    obtenerElementos();
    elementos.btnGenerarReporte.addEventListener("click", generarReporte);
})();
