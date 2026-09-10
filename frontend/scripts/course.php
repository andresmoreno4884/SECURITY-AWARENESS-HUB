<?php
header("Content-Type: application/javascript; charset=UTF-8");
?>

(function () {
    "use strict";

    const cursoAPI = "../../../backend/Api/mis_cursos.php";
    const cursoTitulo = document.getElementById("cursoTitulo");
    const cursoDescripcion = document.getElementById("cursoDescripcion");
    const cursoDuracion = document.getElementById("cursoDuracion");
    const cursoModulos = document.getElementById("cursoModulos");
    const cursoAprobacion = document.getElementById("cursoAprobacion");
    const evaluacionTitulo = document.getElementById("evaluacionTitulo");
    const evaluacionDescripcion = document.getElementById("evaluacionDescripcion");
    const evaluacionPreguntas = document.getElementById("evaluacionPreguntas");
    const evaluacionAprobacion = document.getElementById("evaluacionAprobacion");
    const iniciarEvaluacion = document.getElementById("iniciarEvaluacion");
    const resultadoPorcentaje = document.getElementById("resultadoPorcentaje");
    const resultadoEstado = document.getElementById("resultadoEstado");
    let cursoActual = null;
    let evaluacionActual = null;

    const evaluacionAPI = "../../../backend/Api/mis_evaluaciones.php";

    async function cargarCursoAsignado() {
        try {
            const respuesta = await fetch(cursoAPI, {
                credentials: "same-origin",
                headers: { "Accept": "application/json" }
            });
            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.success) {
                throw new Error(datos.message || "No fue posible cargar el curso.");
            }

            const curso = datos.cursos?.[0];
            if (!curso) return;

            cursoActual = curso;

            cursoTitulo.textContent = curso.titulo || "Curso asignado";
            cursoDescripcion.textContent = curso.descripcion || "Curso de capacitación en seguridad digital.";
            cursoDuracion.textContent = curso.duracion || "Por definir";
            cursoModulos.textContent = curso.numero_modulos || "Por definir";
            cursoAprobacion.textContent = `${curso.porcentaje_aprobacion || 0}%`;
            await cargarEvaluacion(curso.id);
            await cargarResultado();
        } catch (error) {
            console.error("Error al cargar cursos asignados:", error);
        }
    }

    async function cargarEvaluacion(cursoId) {
        const response = await fetch(`${evaluacionAPI}?curso_id=${cursoId}`, {
            credentials: "same-origin"
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message);
        evaluacionActual = data.evaluacion;

        if (!evaluacionActual) {
            iniciarEvaluacion.disabled = true;
            iniciarEvaluacion.textContent = "Evaluación no disponible";
            return;
        }

        evaluacionTitulo.textContent = evaluacionActual.titulo;
        evaluacionDescripcion.textContent = evaluacionActual.descripcion;
        evaluacionPreguntas.innerHTML = `<i class="bi bi-question-circle"></i> ${evaluacionActual.preguntas.length} preguntas`;
        evaluacionAprobacion.innerHTML = `<i class="bi bi-check2-circle"></i> Aprobación: ${evaluacionActual.porcentaje_aprobacion}%`;
    }

    async function cargarResultado() {
        const response = await fetch(`${evaluacionAPI}?resultado=1`, {
            credentials: "same-origin"
        });
        const data = await response.json();
        if (!data.resultado) return;
        resultadoPorcentaje.textContent = `${Number(data.resultado.porcentaje).toFixed(2)}%`;
        resultadoEstado.textContent = data.resultado.aprobado === "si" ? "APROBADO" : "REPROBADO";
    }

    function iniciarFormulario() {
        if (!evaluacionActual) return;
        const modal = document.createElement("div");
        modal.className = "course-evaluation-modal";
        modal.innerHTML = `<div class="course-evaluation-dialog"><button type="button" class="course-evaluation-close">×</button><h2>${evaluacionActual.titulo}</h2><form id="courseEvaluationForm">${evaluacionActual.preguntas.map((pregunta, index) => `<fieldset><legend>${index + 1}. ${pregunta.enunciado}</legend>${pregunta.opciones.map(opcion => `<label><input type="radio" name="question-${pregunta.id}" value="${opcion.id}" required> ${opcion.texto}</label>`).join("")}</fieldset>`).join("")}<button type="submit">Enviar evaluación</button><p id="courseEvaluationMessage"></p></form></div>`;
        document.body.appendChild(modal);
        modal.querySelector(".course-evaluation-close").addEventListener("click", () => modal.remove());
        modal.querySelector("form").addEventListener("submit", async event => {
            event.preventDefault();
            const respuestas = {};
            new FormData(event.target).forEach((value, key) => { respuestas[key.replace("question-", "")] = Number(value); });
            const response = await fetch(evaluacionAPI, { method: "POST", credentials: "same-origin", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ evaluacion_id: evaluacionActual.id, respuestas }) });
            const data = await response.json();
            const message = modal.querySelector("#courseEvaluationMessage");
            message.textContent = data.message;
            if (response.ok && data.success) {
                resultadoPorcentaje.textContent = `${Number(data.resultado.porcentaje).toFixed(2)}%`;
                resultadoEstado.textContent = data.resultado.aprobado === "si" ? "APROBADO" : "REPROBADO";
                iniciarEvaluacion.disabled = true;
                setTimeout(() => modal.remove(), 1200);
            }
        });
    }

    iniciarEvaluacion.addEventListener("click", iniciarFormulario);

    cargarCursoAsignado();
})();
