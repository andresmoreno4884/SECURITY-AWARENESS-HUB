<?php
header("Content-Type: application/javascript; charset=UTF-8");
?>

(function () {
    "use strict";

    const API_URL = "../../backend/Api/resultados.php";
    const cursosURL = "../../backend/Api/courses.php";
    const evaluacionesURL = "../../backend/Api/evaluaciones.php";
    const elementos = {};
    let resultados = [];
    let pagina = 1;
    const porPagina = 8;

    function obtenerElementos() {
        [
            "totalEvaluacionesRealizadas", "promedioGeneral", "totalAprobados",
            "buscarResultado", "filtroCursoResultado", "filtroEvaluacionResultado",
            "filtroEstadoResultado", "btnFiltrarResultados", "resultadosTableBody",
            "contadorResultados", "resultadosMostrados", "paginaAnteriorResultado",
            "paginaActualResultado", "paginaSiguienteResultado", "resultadoModal",
            "cerrarResultadoModal", "cerrarDetalleResultado", "detalleUsuario",
            "detalleCurso", "detalleEvaluacion", "detalleCalificacion",
            "detalleResultado", "detalleFecha"
        ].forEach(id => { elementos[id] = document.getElementById(id); });
    }

    function escapar(valor) {
        const div = document.createElement("div");
        div.textContent = valor ?? "";
        return div.innerHTML;
    }

    async function json(respuesta) {
        const datos = await respuesta.json();
        if (!respuesta.ok || !datos.success) {
            throw new Error(datos.message || "No fue posible cargar los resultados.");
        }
        return datos;
    }

    async function cargarFiltros() {
        const [cursos, evaluaciones] = await Promise.all([
            fetch(cursosURL, { credentials: "same-origin" }).then(json),
            fetch(evaluacionesURL, { credentials: "same-origin" }).then(json)
        ]);

        (cursos.cursos || []).forEach(curso => {
            elementos.filtroCursoResultado.add(new Option(curso.titulo, curso.id));
        });

        (evaluaciones.evaluaciones || []).forEach(evaluacion => {
            elementos.filtroEvaluacionResultado.add(new Option(evaluacion.titulo, evaluacion.id));
        });
    }

    async function cargarResultados() {
        const params = new URLSearchParams();
        const search = elementos.buscarResultado.value.trim();
        const cursoId = elementos.filtroCursoResultado.value;
        const evaluacionId = elementos.filtroEvaluacionResultado.value;
        const estado = elementos.filtroEstadoResultado.value;

        if (search) params.set("search", search);
        if (cursoId !== "todos") params.set("curso_id", cursoId);
        if (evaluacionId !== "todas") params.set("evaluacion_id", evaluacionId);
        if (estado !== "todos") params.set("aprobado", estado === "aprobado" ? "si" : "no");

        try {
            const datos = await fetch(`${API_URL}?${params}`, {
                credentials: "same-origin",
                headers: { "Accept": "application/json" }
            }).then(json);

            resultados = datos.resultados || [];
            elementos.totalEvaluacionesRealizadas.textContent = datos.total ?? 0;
            elementos.promedioGeneral.textContent = `${Number(datos.promedio ?? 0).toFixed(2)}%`;
            elementos.totalAprobados.textContent = datos.aprobados ?? 0;
            pagina = 1;
            renderizar();
        } catch (error) {
            elementos.resultadosTableBody.innerHTML = `<tr><td colspan="7">${escapar(error.message)}</td></tr>`;
        }
    }

    function renderizar() {
        const totalPaginas = Math.max(1, Math.ceil(resultados.length / porPagina));
        pagina = Math.min(pagina, totalPaginas);
        const inicio = (pagina - 1) * porPagina;
        const visibles = resultados.slice(inicio, inicio + porPagina);

        elementos.resultadosTableBody.innerHTML = visibles.length
            ? visibles.map(fila).join("")
            : '<tr><td colspan="7">No hay resultados para mostrar.</td></tr>';
        elementos.contadorResultados.textContent = `${resultados.length} resultados`;
        elementos.resultadosMostrados.textContent = resultados.length
            ? `Mostrando ${inicio + 1} - ${inicio + visibles.length} de ${resultados.length}`
            : "No hay resultados para mostrar";
        elementos.paginaActualResultado.textContent = pagina;
        elementos.paginaAnteriorResultado.disabled = pagina <= 1;
        elementos.paginaSiguienteResultado.disabled = pagina >= totalPaginas;
    }

    function fila(resultado) {
        const aprobado = resultado.aprobado === "si";
        return `<tr>
            <td>${escapar(resultado.usuario)}</td>
            <td>${escapar(resultado.curso || "—")}</td>
            <td>${escapar(resultado.evaluacion)}</td>
            <td>${Number(resultado.porcentaje || 0).toFixed(2)}%</td>
            <td>${aprobado ? "Aprobado" : "Reprobado"}</td>
            <td>${escapar(resultado.fecha_resultado)}</td>
            <td><button type="button" class="result-action-button" data-result-id="${resultado.id}">Ver detalle</button></td>
        </tr>`;
    }

    function abrirDetalle(id) {
        const resultado = resultados.find(item => Number(item.id) === Number(id));
        if (!resultado) return;
        elementos.detalleUsuario.textContent = resultado.usuario || "—";
        elementos.detalleCurso.textContent = resultado.curso || "—";
        elementos.detalleEvaluacion.textContent = resultado.evaluacion || "—";
        elementos.detalleCalificacion.textContent = `${Number(resultado.porcentaje || 0).toFixed(2)}% (${resultado.preguntas_correctas}/${resultado.preguntas_totales})`;
        elementos.detalleResultado.textContent = resultado.aprobado === "si" ? "Aprobado" : "Reprobado";
        elementos.detalleFecha.textContent = resultado.fecha_resultado || "—";
        elementos.resultadoModal.hidden = false;
    }

    function cerrarDetalle() {
        elementos.resultadoModal.hidden = true;
    }

    function iniciar() {
        obtenerElementos();
        elementos.btnFiltrarResultados.addEventListener("click", cargarResultados);
        elementos.buscarResultado.addEventListener("keydown", evento => {
            if (evento.key === "Enter") cargarResultados();
        });
        elementos.paginaAnteriorResultado.addEventListener("click", () => { pagina--; renderizar(); });
        elementos.paginaSiguienteResultado.addEventListener("click", () => { pagina++; renderizar(); });
        elementos.resultadosTableBody.addEventListener("click", evento => {
            const boton = evento.target.closest("button[data-result-id]");
            if (boton) abrirDetalle(boton.dataset.resultId);
        });
        elementos.cerrarResultadoModal.addEventListener("click", cerrarDetalle);
        elementos.cerrarDetalleResultado.addEventListener("click", cerrarDetalle);
        elementos.resultadoModal.querySelector(".user-modal-overlay").addEventListener("click", cerrarDetalle);
        Promise.all([cargarFiltros(), cargarResultados()]).catch(error => {
            elementos.resultadosTableBody.innerHTML = `<tr><td colspan="7">${escapar(error.message)}</td></tr>`;
        });
    }

    iniciar();
})();
