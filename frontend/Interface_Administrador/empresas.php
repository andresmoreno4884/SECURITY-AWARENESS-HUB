<?php require_once __DIR__ . '/../includes/page_context.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Empresas | Security Awareness Hub
    </title>


    <link
        rel="stylesheet"
        href="css/empresas.css">

</head>

<body>



    <section class="companies-panel">




        <div class="dashboard-heading">

            <div>

                <span class="section-label">
                    GESTIÓN DE EMPRESAS
                </span>

                <h2>
                    Empresas
                </h2>

                <p>
                    Administra las empresas registradas y consulta
                    la información relacionada con la plataforma.
                </p>

            </div>


            <button
                type="button"
                class="admin-button"
                id="btnNuevaEmpresa">

                + Nueva empresa

            </button>

        </div>




        <div class="dashboard-cards">




            <div class="dashboard-card">

                <div class="card-icon">
                    ▣
                </div>

                <div>

                    <span>
                        EMPRESAS
                    </span>

                    <strong id="totalEmpresas">
                        0
                    </strong>

                    <small>
                        Empresas registradas
                    </small>

                </div>

            </div>




            <div class="dashboard-card">

                <div class="card-icon">
                    ✓
                </div>

                <div>

                    <span>
                        ACTIVAS
                    </span>

                    <strong id="empresasActivas">
                        0
                    </strong>

                    <small>
                        Empresas activas
                    </small>

                </div>

            </div>




            <div class="dashboard-card">

                <div class="card-icon">
                    ◉
                </div>

                <div>

                    <span>
                        INACTIVAS
                    </span>

                    <strong id="empresasInactivas">
                        0
                    </strong>

                    <small>
                        Empresas inactivas
                    </small>

                </div>

            </div>

        </div>




        <div class="users-toolbar">




            <div class="search-box">

                <span class="search-icon">
                    ⌕
                </span>

                <input
                    type="search"
                    id="buscarEmpresa"
                    placeholder="Buscar por nombre, NIT o correo..."
                    autocomplete="off">

            </div>




            <select
                class="users-filter"
                id="filtroEstadoEmpresa"
                title="Filtrar empresas por estado">

                <option value="todos">
                    Todos los estados
                </option>

                <option value="activo">
                    Activas
                </option>

                <option value="inactivo">
                    Inactivas
                </option>

            </select>




            <select
                class="users-filter"
                id="filtroTipoEmpresa"
                title="Filtrar empresas por tipo">

                <option value="todos">
                    Todos los tipos
                </option>

                <option value="privada">
                    Privada
                </option>

                <option value="publica">
                    Pública
                </option>

                <option value="mixta">
                    Mixta
                </option>

            </select>

        </div>




        <div class="dashboard-card users-table-card">




            <div class="card-header">

                <div>

                    <span class="section-label">
                        DIRECTORIO
                    </span>

                    <h3>
                        Empresas registradas
                    </h3>

                </div>


                <span
                    class="card-period"
                    id="contadorEmpresas">

                    Cargando...

                </span>

            </div>




            <div class="table-wrapper">

                <table class="users-table companies-table">

                    <thead>

                        <tr>

                            <th>
                                EMPRESA
                            </th>

                            <th>
                                NIT
                            </th>

                            <th>
                                CORREO
                            </th>

                            <th>
                                TELÉFONO
                            </th>

                            <th>
                                ESTADO
                            </th>

                            <th>
                                FECHA DE REGISTRO
                            </th>

                            <th>
                                ACCIONES
                            </th>

                        </tr>

                    </thead>




                    <tbody id="empresasTableBody">

                        <tr>

                            <td
                                colspan="7"
                                class="loading-users">

                                Cargando empresas...

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>




            <div class="table-footer">

                <span id="empresasMostradas">
                    Cargando empresas...
                </span>


                <div class="pagination">

                    <button
                        type="button"
                        class="pagination-button"
                        id="paginaAnteriorEmpresa"
                        disabled>

                        ←

                    </button>


                    <button
                        type="button"
                        class="pagination-button active"
                        id="paginaActualEmpresa">

                        1

                    </button>


                    <button
                        type="button"
                        class="pagination-button"
                        id="paginaSiguienteEmpresa"
                        disabled>

                        →

                    </button>

                </div>

            </div>

        </div>




        <div
            class="user-modal"
            id="empresaModal"
            hidden>

            <div class="user-modal-overlay"></div>


            <div
                class="user-modal-content"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modalEmpresaTitulo">




                <div class="user-modal-header">

                    <div>

                        <span class="section-label">
                            ADMINISTRACIÓN
                        </span>

                        <h3 id="modalEmpresaTitulo">
                            Nueva empresa
                        </h3>

                    </div>


                    <button
                        type="button"
                        class="modal-close"
                        id="cerrarEmpresaModal"
                        aria-label="Cerrar">

                        ×

                    </button>

                </div>




                <form id="formEmpresa">




                    <input
                        type="hidden"
                        id="empresaId">




                    <div class="form-group">

                        <label for="empresaNombre">
                            Nombre de la empresa
                        </label>

                        <input
                            type="text"
                            id="empresaNombre"
                            name="nombre"
                            placeholder="Ingrese el nombre de la empresa"
                            maxlength="150"
                            required>

                    </div>




                    <div class="form-group">

                        <label for="empresaNit">
                            NIT
                        </label>

                        <input
                            type="text"
                            id="empresaNit"
                            name="nit"
                            placeholder="Ingrese el NIT"
                            maxlength="30"
                            required>

                    </div>




                    <div class="form-group">

                        <label for="empresaCorreo">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            id="empresaCorreo"
                            name="correo"
                            placeholder="empresa@correo.com"
                            maxlength="150"
                            required>

                    </div>




                    <div class="form-group">

                        <label for="empresaTelefono">
                            Teléfono
                        </label>

                        <input
                            type="tel"
                            id="empresaTelefono"
                            name="telefono"
                            placeholder="Ingrese el número de teléfono"
                            maxlength="30">

                    </div>




                    <div class="form-group">

                        <label for="empresaDireccion">
                            Dirección
                        </label>

                        <input
                            type="text"
                            id="empresaDireccion"
                            name="direccion"
                            placeholder="Ingrese la dirección"
                            maxlength="200">

                    </div>




                    <div class="form-group">

                        <label for="empresaTipo">
                            Tipo de empresa
                        </label>

                        <select
                            id="empresaTipo"
                            name="tipo"
                            title="Seleccionar tipo de empresa"
                            required>

                            <option value="privada">
                                Privada
                            </option>

                            <option value="publica">
                                Pública
                            </option>

                            <option value="mixta">
                                Mixta
                            </option>

                        </select>

                    </div>




                    <div class="form-group">

                        <label for="empresaEstado">
                            Estado
                        </label>

                        <select
                            id="empresaEstado"
                            name="estado"
                            title="Seleccionar estado de la empresa"
                            required>

                            <option value="activo">
                                Activa
                            </option>

                            <option value="inactivo">
                                Inactiva
                            </option>

                        </select>

                    </div>




                    <div
                        id="empresaFormMessage"
                        class="user-form-message"
                        aria-live="polite">
                    </div>




                    <div class="user-modal-actions">

                        <button
                            type="button"
                            class="modal-button secondary"
                            id="cancelarEmpresa">

                            Cancelar

                        </button>


                        <button
                            type="submit"
                            class="modal-button primary"
                            id="guardarEmpresa">

                            Crear empresa

                        </button>

                    </div>

                </form>

            </div>

        </div>


    </section>

</body>

</html>
