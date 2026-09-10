<?php
header("Content-Type: application/javascript; charset=UTF-8");
?>

(function () {
    "use strict";

    const API_URL = "../../backend/Api/asignaciones.php";
    const usuariosURL = "../../backend/Api/usuarios.php";
    const cursosURL = "../../backend/Api/courses.php";
    const evaluacionesURL = "../../backend/Api/evaluaciones.php";
    const elementos = {};
    let asignaciones = [];
    let filtradas = [];
    let pagina = 1;
    const porPagina = 8;

    function obtenerElementos() {
        [
            "totalAsignaciones", "asignacionesActivas", "asignacionesCompletadas",
            "buscarAsignacion", "filtroEstadoAsignacion", "filtroTipoAsignacion",
            "asignacionesTableBody", "contadorAsignaciones", "asignacionesMostradas",
            "paginaAnteriorAsignaciones", "paginaActualAsignaciones", "paginaSiguienteAsignaciones",
            "btnNuevaAsignacion", "asignacionModal", "asignacionModalOverlay",
            "cerrarAsignacionModal", "cancelarAsignacion", "formAsignacion",
            "asignacionId", "asignacionUsuario", "asignacionCurso", "asignacionEvaluacion", "asignacionTipo",
            "asignacionFecha", "asignacionEstado", "asignacionFormMessage",
            "guardarAsignacion", "modalAsignacionTitulo"
        ].forEach(id => {
            elementos[id] = document.getElementById(id);
        });
    }

    function escapar(valor) {
        const div = document.createElement("div");
        div.textContent = valor ?? "";
        return div.innerHTML;
    }

    async function json(respuesta) {
        const datos = await respuesta.json();
        if (!respuesta.ok || !datos.success) {
            throw new Error(datos.message || "No fue posible completar la operación.");
        }
        return datos;
    }

    async function cargarOpciones() {
        const [usuariosRespuesta, cursosRespuesta, evaluacionesRespuesta] = await Promise.all([
            fetch(usuariosURL, { credentials: "same-origin" }).then(json),
            fetch(cursosURL, { credentials: "same-origin" }).then(json),
            fetch(evaluacionesURL, { credentials: "same-origin" }).then(json)
        ]);

        (usuariosRespuesta.usuarios || []).forEach(usuario => {
            elementos.asignacionUsuario.add(new Option(
                `${usuario.nombres} ${usuario.apellidos} - ${usuario.correo}`,
                usuario.id
            ));
        });

        (cursosRespuesta.cursos || []).filter(curso => curso.estado === "activo").forEach(curso => {
            elementos.asignacionCurso.add(new Option(curso.titulo, curso.id));
        });

        (evaluacionesRespuesta.evaluaciones || []).filter(evaluacion => evaluacion.estado === "activo").forEach(evaluacion => {
            elementos.asignacionEvaluacion.add(new Option(
                `${evaluacion.titulo} - ${evaluacion.curso_titulo}`,
                evaluacion.id
            ));
        });
    }

    async function cargarAsignaciones() {
        const params = new URLSearchParams();
        const search = elementos.buscarAsignacion.value.trim();
        const estado = elementos.filtroEstadoAsignacion.value;
        const tipo = elementos.filtroTipoAsignacion.value;

        if (search) params.set("search", search);
        if (estado !== "todos") params.set("estado", estado);
        if (tipo !== "todos") params.set("tipo", tipo);

        try {
            const datos = await fetch(`${API_URL}?${params}`, {
                credentials: "same-origin",
                headers: { "Accept": "application/json" }
            }).then(json);

            asignaciones = datos.asignaciones || [];
            elementos.totalAsignaciones.textContent = datos.total ?? 0;
            elementos.asignacionesActivas.textContent = datos.activas ?? 0;
            elementos.asignacionesCompletadas.textContent = datos.completadas ?? 0;
            aplicarPaginacion();
        } catch (error) {
            elementos.asignacionesTableBody.innerHTML = `<tr><td colspan="6">${escapar(error.message)}</td></tr>`;
        }
    }

    function aplicarPaginacion() {
        filtradas = asignaciones;
        const totalPaginas = Math.max(1, Math.ceil(filtradas.length / porPagina));
        pagina = Math.min(pagina, totalPaginas);
        const inicio = (pagina - 1) * porPagina;
        const paginaActual = filtradas.slice(inicio, inicio + porPagina);

        elementos.asignacionesTableBody.innerHTML = paginaActual.length
            ? paginaActual.map(fila).join("")
            : '<tr><td colspan="6">No hay asignaciones para mostrar.</td></tr>';
        elementos.contadorAsignaciones.textContent = `${filtradas.length} asignaciones`;
        elementos.asignacionesMostradas.textContent = filtradas.length
            ? `Mostrando ${inicio + 1} - ${inicio + paginaActual.length} de ${filtradas.length}`
            : "No hay asignaciones para mostrar";
        elementos.paginaActualAsignaciones.textContent = pagina;
        elementos.paginaAnteriorAsignaciones.disabled = pagina <= 1;
        elementos.paginaSiguienteAsignaciones.disabled = pagina >= totalPaginas;
    }

    function fila(asignacion) {
        const recurso = asignacion.tipo === "evaluacion"
            ? asignacion.evaluacion || "Evaluación"
            : asignacion.curso || "Curso";
        const estado = asignacion.estado || "pendiente";
        return `<tr>
            <td>${escapar(asignacion.usuario)}</td>
            <td>${escapar(recurso)}</td>
            <td>${escapar(asignacion.tipo)}</td>
            <td>${escapar(estado)}</td>
            <td>${escapar(asignacion.fecha_asignacion)}</td>
            <td><div class="table-actions">
                <button type="button" class="table-action status" data-action="estado" data-id="${asignacion.id}">Cambiar estado</button>
                <button type="button" class="table-action delete" data-action="eliminar" data-id="${asignacion.id}">Eliminar</button>
            </div></td>
        </tr>`;
    }

    function abrirNueva() {
        elementos.formAsignacion.reset();
        elementos.asignacionId.value = "";
        elementos.asignacionFecha.value = new Date().toISOString().slice(0, 10);
        elementos.modalAsignacionTitulo.textContent = "Nueva asignación";
        elementos.guardarAsignacion.textContent = "Crear asignación";
        elementos.asignacionModal.hidden = false;
    }

    function cerrar() {
        elementos.asignacionModal.hidden = true;
        elementos.asignacionFormMessage.textContent = "";
    }

    async function guardar(evento) {
        evento.preventDefault();
        const datos = {
            usuario_id: Number(elementos.asignacionUsuario.value),
            curso_id: Number(elementos.asignacionCurso.value),
            tipo: elementos.asignacionTipo.value,
            fecha_asignacion: elementos.asignacionFecha.value,
            estado: elementos.asignacionEstado.value
        };
        if (elementos.asignacionId.value) {
            datos.id = Number(elementos.asignacionId.value);
        }

        if (datos.tipo === "evaluacion") {
            datos.evaluacion_id = Number(elementos.asignacionEvaluacion.value);
            datos.curso_id = 0;
        }

        try {
            const resultado = await fetch(API_URL, {
                method: datos.id ? "PUT" : "POST",
                credentials: "same-origin",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(datos)
            }).then(json);
            elementos.asignacionFormMessage.textContent = resultado.message;
            cerrar();
            await cargarAsignaciones();
        } catch (error) {
            elementos.asignacionFormMessage.textContent = error.message;
        }
    }

    async function cambiarEstado(id) {
        const asignacion = asignaciones.find(item => Number(item.id) === Number(id));
        const estados = ["pendiente", "progreso", "completado"];
        const siguiente = estados[(estados.indexOf(asignacion.estado) + 1) % estados.length];
        await fetch(API_URL, {
            method: "PATCH",
            credentials: "same-origin",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: Number(id), estado: siguiente })
        }).then(json);
        await cargarAsignaciones();
    }

    async function eliminar(id) {
        if (!window.confirm("¿Eliminar esta asignación?")) return;
        await fetch(API_URL, {
            method: "DELETE",
            credentials: "same-origin",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: Number(id) })
        }).then(json);
        await cargarAsignaciones();
    }

    function iniciar() {
        obtenerElementos();
        elementos.btnNuevaAsignacion.addEventListener("click", abrirNueva);
        elementos.cerrarAsignacionModal.addEventListener("click", cerrar);
        elementos.cancelarAsignacion.addEventListener("click", cerrar);
        elementos.asignacionModalOverlay.addEventListener("click", cerrar);
        elementos.formAsignacion.addEventListener("submit", guardar);
        elementos.asignacionTipo.addEventListener("change", () => {
            const esEvaluacion = elementos.asignacionTipo.value === "evaluacion";
            elementos.asignacionCurso.required = !esEvaluacion;
            elementos.asignacionEvaluacion.required = esEvaluacion;
            elementos.asignacionCurso.disabled = esEvaluacion;
            elementos.asignacionEvaluacion.disabled = !esEvaluacion;
        });
        elementos.buscarAsignacion.addEventListener("input", cargarAsignaciones);
        elementos.filtroEstadoAsignacion.addEventListener("change", cargarAsignaciones);
        elementos.filtroTipoAsignacion.addEventListener("change", cargarAsignaciones);
        elementos.paginaAnteriorAsignaciones.addEventListener("click", () => { pagina--; aplicarPaginacion(); });
        elementos.paginaSiguienteAsignaciones.addEventListener("click", () => { pagina++; aplicarPaginacion(); });
        elementos.asignacionesTableBody.addEventListener("click", evento => {
            const boton = evento.target.closest("button[data-action]");
            if (!boton) return;
            const accion = boton.dataset.action;
            (accion === "estado" ? cambiarEstado : eliminar)(boton.dataset.id);
        });
        Promise.all([cargarOpciones(), cargarAsignaciones()]).catch(error => {
            elementos.asignacionesTableBody.innerHTML = `<tr><td colspan="6">${escapar(error.message)}</td></tr>`;
        });
    }

    iniciar();
})();
