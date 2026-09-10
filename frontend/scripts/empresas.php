<?php
header("Content-Type: application/javascript; charset=UTF-8");
?>

(function () {
    "use strict";

    const API_URL = "../../backend/Api/empresas.php";

    let empresas = [];
    let empresasFiltradas = [];
    let paginaActual = 1;
    const empresasPorPagina = 8;

    const elementos = {};

    function obtenerElementos() {
        elementos.totalEmpresas = document.getElementById("totalEmpresas");
        elementos.empresasActivas = document.getElementById("empresasActivas");
        elementos.empresasInactivas = document.getElementById("empresasInactivas");

        elementos.buscarEmpresa = document.getElementById("buscarEmpresa");
        elementos.filtroEstadoEmpresa = document.getElementById("filtroEstadoEmpresa");
        elementos.filtroTipoEmpresa = document.getElementById("filtroTipoEmpresa");

        elementos.empresasTableBody = document.getElementById("empresasTableBody");
        elementos.contadorEmpresas = document.getElementById("contadorEmpresas");
        elementos.empresasMostradas = document.getElementById("empresasMostradas");

        elementos.paginaAnteriorEmpresa = document.getElementById("paginaAnteriorEmpresa");
        elementos.paginaActualEmpresa = document.getElementById("paginaActualEmpresa");
        elementos.paginaSiguienteEmpresa = document.getElementById("paginaSiguienteEmpresa");

        elementos.btnNuevaEmpresa = document.getElementById("btnNuevaEmpresa");

        elementos.empresaModal = document.getElementById("empresaModal");
        elementos.cerrarEmpresaModal = document.getElementById("cerrarEmpresaModal");
        elementos.cancelarEmpresa = document.getElementById("cancelarEmpresa");

        elementos.formEmpresa = document.getElementById("formEmpresa");

        elementos.empresaId = document.getElementById("empresaId");
        elementos.empresaNombre = document.getElementById("empresaNombre");
        elementos.empresaNit = document.getElementById("empresaNit");
        elementos.empresaCorreo = document.getElementById("empresaCorreo");
        elementos.empresaTelefono = document.getElementById("empresaTelefono");
        elementos.empresaDireccion = document.getElementById("empresaDireccion");
        elementos.empresaTipo = document.getElementById("empresaTipo");
        elementos.empresaEstado = document.getElementById("empresaEstado");

        elementos.empresaFormMessage = document.getElementById("empresaFormMessage");
        elementos.guardarEmpresa = document.getElementById("guardarEmpresa");
        elementos.modalEmpresaTitulo = document.getElementById("modalEmpresaTitulo");
    }

    function escaparHTML(valor) {
        const div = document.createElement("div");
        div.textContent = valor ?? "";
        return div.innerHTML;
    }

    function mostrarMensaje(mensaje, tipo = "error") {
        if (!elementos.empresaFormMessage) {
            return;
        }

        elementos.empresaFormMessage.textContent = mensaje;
        elementos.empresaFormMessage.className =
            "user-form-message " + tipo;
    }

    function limpiarMensaje() {
        if (!elementos.empresaFormMessage) {
            return;
        }

        elementos.empresaFormMessage.textContent = "";
        elementos.empresaFormMessage.className = "user-form-message";
    }

    async function obtenerEmpresas() {
        try {
            elementos.empresasTableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="loading-users">
                        Cargando empresas...
                    </td>
                </tr>
            `;

            const respuesta = await fetch(API_URL, {
                method: "GET",
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json"
                }
            });

            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.success) {
                throw new Error(
                    datos.message || "No fue posible cargar las empresas."
                );
            }

            empresas = Array.isArray(datos.empresas)
                ? datos.empresas
                : [];

            actualizarIndicadores(datos);
            aplicarFiltros();

        } catch (error) {
            console.error("Error al cargar empresas:", error);

            empresas = [];
            empresasFiltradas = [];

            elementos.empresasTableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="loading-users">
                        No fue posible cargar las empresas.
                    </td>
                </tr>
            `;

            elementos.contadorEmpresas.textContent = "Error";
            elementos.empresasMostradas.textContent =
                "No se pudieron cargar las empresas.";
        }
    }

    function actualizarIndicadores(datos) {
        const total = Number(datos.total ?? empresas.length);
        const activas = Number(datos.activas ?? 0);
        const inactivas = Number(datos.inactivas ?? 0);

        elementos.totalEmpresas.textContent = total;
        elementos.empresasActivas.textContent = activas;
        elementos.empresasInactivas.textContent = inactivas;
    }

    function aplicarFiltros() {
        const texto = elementos.buscarEmpresa.value
            .trim()
            .toLowerCase();

        const estado = elementos.filtroEstadoEmpresa.value;
        const tipo = elementos.filtroTipoEmpresa.value;

        empresasFiltradas = empresas.filter((empresa) => {
            const coincideTexto =
                texto === "" ||
                String(empresa.nombre ?? "")
                    .toLowerCase()
                    .includes(texto) ||
                String(empresa.nit ?? "")
                    .toLowerCase()
                    .includes(texto) ||
                String(empresa.correo ?? "")
                    .toLowerCase()
                    .includes(texto);

            const coincideEstado =
                estado === "todos" ||
                empresa.estado === estado;

            const coincideTipo =
                tipo === "todos" ||
                empresa.tipo === tipo;

            return coincideTexto &&
                coincideEstado &&
                coincideTipo;
        });

        paginaActual = 1;
        renderizarTabla();
    }

    function renderizarTabla() {
        const total = empresasFiltradas.length;

        const totalPaginas = Math.max(
            1,
            Math.ceil(total / empresasPorPagina)
        );

        if (paginaActual > totalPaginas) {
            paginaActual = totalPaginas;
        }

        const inicio =
            (paginaActual - 1) * empresasPorPagina;

        const fin =
            inicio + empresasPorPagina;

        const empresasPagina =
            empresasFiltradas.slice(inicio, fin);

        if (empresasPagina.length === 0) {
            elementos.empresasTableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="loading-users">
                        No hay empresas que coincidan con los filtros.
                    </td>
                </tr>
            `;
        } else {
            elementos.empresasTableBody.innerHTML =
                empresasPagina
                    .map(crearFilaEmpresa)
                    .join("");
        }

        actualizarPaginacion(
            total,
            totalPaginas,
            inicio,
            empresasPagina.length
        );
    }

    function crearFilaEmpresa(empresa) {
        const id = Number(empresa.id);

        const nombre = escaparHTML(empresa.nombre);
        const nit = escaparHTML(empresa.nit);
        const correo = escaparHTML(empresa.correo);
        const telefono = escaparHTML(
            empresa.telefono || "—"
        );

        const estado = empresa.estado === "activo"
            ? "Activa"
            : "Inactiva";

        const estadoClase = empresa.estado === "activo"
            ? "active"
            : "inactive";

        const fecha = formatearFecha(
            empresa.fecha_registro
        );

        return `
            <tr>
                <td>
                    <strong>${nombre}</strong>
                </td>

                <td>
                    ${nit}
                </td>

                <td>
                    ${correo}
                </td>

                <td>
                    ${telefono}
                </td>

                <td>
                    <span class="status-badge ${estadoClase}">
                        ${estado}
                    </span>
                </td>

                <td>
                    ${fecha}
                </td>

                <td>
                    <div class="table-actions">

                        <button
                            type="button"
                            class="table-action-button edit"
                            data-action="editar"
                            data-id="${id}">
                            Editar
                        </button>

                        <button
                            type="button"
                            class="table-action-button status"
                            data-action="estado"
                            data-id="${id}">
                            ${empresa.estado === "activo"
                                ? "Desactivar"
                                : "Activar"}
                        </button>

                        <button
                            type="button"
                            class="table-action-button delete"
                            data-action="eliminar"
                            data-id="${id}">
                            Eliminar
                        </button>

                    </div>
                </td>
            </tr>
        `;
    }

    function formatearFecha(fecha) {
        if (!fecha) {
            return "—";
        }

        const fechaObjeto = new Date(
            String(fecha).replace(" ", "T")
        );

        if (Number.isNaN(fechaObjeto.getTime())) {
            return escaparHTML(fecha);
        }

        return fechaObjeto.toLocaleDateString(
            "es-CO",
            {
                year: "numeric",
                month: "2-digit",
                day: "2-digit"
            }
        );
    }

    function actualizarPaginacion(
        total,
        totalPaginas,
        inicio,
        cantidadPagina
    ) {
        elementos.paginaActualEmpresa.textContent =
            paginaActual;

        elementos.paginaAnteriorEmpresa.disabled =
            paginaActual <= 1;

        elementos.paginaSiguienteEmpresa.disabled =
            paginaActual >= totalPaginas;

        if (total === 0) {
            elementos.contadorEmpresas.textContent =
                "0 empresas";

            elementos.empresasMostradas.textContent =
                "No hay empresas para mostrar";

            return;
        }

        elementos.contadorEmpresas.textContent =
            `${total} ${total === 1 ? "empresa" : "empresas"}`;

        elementos.empresasMostradas.textContent =
            `Mostrando ${inicio + 1} - ${
                inicio + cantidadPagina
            } de ${total} empresas`;
    }

    function abrirModalNuevaEmpresa() {
        elementos.formEmpresa.reset();

        elementos.empresaId.value = "";

        elementos.empresaTipo.value = "privada";
        elementos.empresaEstado.value = "activo";

        elementos.modalEmpresaTitulo.textContent =
            "Nueva empresa";

        elementos.guardarEmpresa.textContent =
            "Crear empresa";

        limpiarMensaje();

        elementos.empresaModal.hidden = false;

        elementos.empresaNombre.focus();
    }

    function abrirModalEditarEmpresa(id) {
        const empresa = empresas.find(
            (item) => Number(item.id) === Number(id)
        );

        if (!empresa) {
            return;
        }

        elementos.empresaId.value = empresa.id;
        elementos.empresaNombre.value =
            empresa.nombre || "";

        elementos.empresaNit.value =
            empresa.nit || "";

        elementos.empresaCorreo.value =
            empresa.correo || "";

        elementos.empresaTelefono.value =
            empresa.telefono || "";

        elementos.empresaDireccion.value =
            empresa.direccion || "";

        elementos.empresaTipo.value =
            empresa.tipo || "privada";

        elementos.empresaEstado.value =
            empresa.estado || "activo";

        elementos.modalEmpresaTitulo.textContent =
            "Editar empresa";

        elementos.guardarEmpresa.textContent =
            "Guardar cambios";

        limpiarMensaje();

        elementos.empresaModal.hidden = false;

        elementos.empresaNombre.focus();
    }

    function cerrarModal() {
        elementos.empresaModal.hidden = true;
        limpiarMensaje();
    }

    async function guardarEmpresa(evento) {
        evento.preventDefault();

        limpiarMensaje();

        const id = elementos.empresaId.value.trim();

        const datos = {
            nombre: elementos.empresaNombre.value.trim(),
            nit: elementos.empresaNit.value.trim(),
            correo: elementos.empresaCorreo.value.trim(),
            telefono: elementos.empresaTelefono.value.trim(),
            direccion: elementos.empresaDireccion.value.trim(),
            tipo: elementos.empresaTipo.value,
            estado: elementos.empresaEstado.value
        };

        if (!datos.nombre ||
            !datos.nit ||
            !datos.correo) {

            mostrarMensaje(
                "Nombre, NIT y correo son obligatorios.",
                "error"
            );

            return;
        }

        if (!elementos.formEmpresa.checkValidity()) {
            elementos.formEmpresa.reportValidity();
            return;
        }

        const esEdicion = id !== "";

        if (esEdicion) {
            datos.id = Number(id);
        }

        elementos.guardarEmpresa.disabled = true;
        elementos.guardarEmpresa.textContent =
            esEdicion
                ? "Guardando..."
                : "Creando...";

        try {
            const respuesta = await fetch(API_URL, {
                method: esEdicion ? "PUT" : "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(datos)
            });

            const resultado = await respuesta.json();

            if (!respuesta.ok || !resultado.success) {
                throw new Error(
                    resultado.message ||
                    "No fue posible guardar la empresa."
                );
            }

            cerrarModal();

            await obtenerEmpresas();

        } catch (error) {
            console.error(
                "Error al guardar empresa:",
                error
            );

            mostrarMensaje(
                error.message ||
                "Ocurrió un error al guardar la empresa.",
                "error"
            );

        } finally {
            elementos.guardarEmpresa.disabled = false;

            elementos.guardarEmpresa.textContent =
                esEdicion
                    ? "Guardar cambios"
                    : "Crear empresa";
        }
    }

    async function cambiarEstadoEmpresa(id) {
        const empresa = empresas.find(
            (item) => Number(item.id) === Number(id)
        );

        if (!empresa) {
            return;
        }

        const nuevoEstado =
            empresa.estado === "activo"
                ? "inactivo"
                : "activo";

        const accion =
            nuevoEstado === "activo"
                ? "activar"
                : "desactivar";

        const confirmado = window.confirm(
            `¿Deseas ${accion} la empresa "${empresa.nombre}"?`
        );

        if (!confirmado) {
            return;
        }

        try {
            const respuesta = await fetch(API_URL, {
                method: "PATCH",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    id: Number(id),
                    estado: nuevoEstado
                })
            });

            const resultado = await respuesta.json();

            if (!respuesta.ok || !resultado.success) {
                throw new Error(
                    resultado.message ||
                    "No fue posible cambiar el estado."
                );
            }

            await obtenerEmpresas();

        } catch (error) {
            console.error(
                "Error al cambiar estado:",
                error
            );

            window.alert(
                error.message ||
                "No fue posible cambiar el estado de la empresa."
            );
        }
    }

    async function eliminarEmpresa(id) {
        const empresa = empresas.find(
            (item) => Number(item.id) === Number(id)
        );

        if (!empresa) {
            return;
        }

        const confirmado = window.confirm(
            `¿Estás seguro de eliminar la empresa "${empresa.nombre}"?\n\nEsta acción no se puede deshacer.`
        );

        if (!confirmado) {
            return;
        }

        try {
            const respuesta = await fetch(API_URL, {
                method: "DELETE",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    id: Number(id)
                })
            });

            const resultado = await respuesta.json();

            if (!respuesta.ok || !resultado.success) {
                throw new Error(
                    resultado.message ||
                    "No fue posible eliminar la empresa."
                );
            }

            await obtenerEmpresas();

        } catch (error) {
            console.error(
                "Error al eliminar empresa:",
                error
            );

            window.alert(
                error.message ||
                "No fue posible eliminar la empresa."
            );
        }
    }

    function manejarAccionesTabla(evento) {
        const boton =
            evento.target.closest(
                "[data-action]"
            );

        if (!boton) {
            return;
        }

        const id = Number(
            boton.dataset.id
        );

        const accion =
            boton.dataset.action;

        if (!id) {
            return;
        }

        if (accion === "editar") {
            abrirModalEditarEmpresa(id);
        }

        if (accion === "estado") {
            cambiarEstadoEmpresa(id);
        }

        if (accion === "eliminar") {
            eliminarEmpresa(id);
        }
    }

    function configurarEventos() {
        elementos.btnNuevaEmpresa.addEventListener(
            "click",
            abrirModalNuevaEmpresa
        );

        elementos.cerrarEmpresaModal.addEventListener(
            "click",
            cerrarModal
        );

        elementos.cancelarEmpresa.addEventListener(
            "click",
            cerrarModal
        );

        elementos.formEmpresa.addEventListener(
            "submit",
            guardarEmpresa
        );

        elementos.empresasTableBody.addEventListener(
            "click",
            manejarAccionesTabla
        );

        elementos.buscarEmpresa.addEventListener(
            "input",
            aplicarFiltros
        );

        elementos.filtroEstadoEmpresa.addEventListener(
            "change",
            aplicarFiltros
        );

        elementos.filtroTipoEmpresa.addEventListener(
            "change",
            aplicarFiltros
        );

        elementos.paginaAnteriorEmpresa.addEventListener(
            "click",
            function () {
                if (paginaActual > 1) {
                    paginaActual--;
                    renderizarTabla();
                }
            }
        );

        elementos.paginaSiguienteEmpresa.addEventListener(
            "click",
            function () {
                const totalPaginas = Math.max(
                    1,
                    Math.ceil(
                        empresasFiltradas.length /
                        empresasPorPagina
                    )
                );

                if (paginaActual < totalPaginas) {
                    paginaActual++;
                    renderizarTabla();
                }
            }
        );

        elementos.empresaModal
            .querySelector(".user-modal-overlay")
            .addEventListener(
                "click",
                cerrarModal
            );

        document.addEventListener(
            "keydown",
            function (evento) {
                if (
                    evento.key === "Escape" &&
                    !elementos.empresaModal.hidden
                ) {
                    cerrarModal();
                }
            }
        );
    }

    function inicializarEmpresas() {
        const vistaActual =
            document.getElementById("empresasTableBody");

        if (!vistaActual) {
            return;
        }

        obtenerElementos();
        configurarEventos();
        obtenerEmpresas();
    }

    if (document.readyState === "loading") {
        document.addEventListener(
            "DOMContentLoaded",
            inicializarEmpresas
        );
    } else {
        inicializarEmpresas();
    }

})();