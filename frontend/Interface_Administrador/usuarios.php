<?php require_once __DIR__ . '/../includes/page_context.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Usuarios | Security Awareness Hub
    </title>


    <link
        rel="stylesheet"
        href="css/usuarios.css?v=2">

</head>

<body>



    <section class="users-panel">




        <div class="dashboard-heading">

            <div>

                <span class="section-label">
                    GESTIÓN DE USUARIOS
                </span>

                <h2>
                    Usuarios
                </h2>

                <p>
                    Administra los usuarios y consulta
                    su información dentro de la plataforma.
                </p>

            </div>


            <button
                type="button"
                class="admin-button"
                id="btnNuevoUsuario">

                + Nuevo usuario

            </button>

        </div>




        <div class="users-toolbar">




            <div class="search-box">

                <span class="search-icon">
                    ⌕
                </span>

                <input
                    type="search"
                    id="buscarUsuario"
                    placeholder="Buscar por nombre, apellido o correo..."
                    autocomplete="off">

            </div>




            <select
                class="users-filter"
                id="filtroEstado"
                title="Filtrar por estado">

                <option value="todos">
                    Todos los estados
                </option>

                <option value="activo">
                    Activos
                </option>

                <option value="inactivo">
                    Inactivos
                </option>

            </select>




            <select
                class="users-filter"
                id="filtroRol"
                title="Filtrar por rol">

                <option value="todos">
                    Todos los roles
                </option>

                <option value="usuario">
                    Usuarios
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
                        Usuarios registrados
                    </h3>

                </div>


                <span
                    class="card-period"
                    id="contadorUsuarios">

                    Cargando...

                </span>

            </div>




            <div class="table-wrapper">

                <table class="users-table">

                    <thead>

                        <tr>

                            <th>
                                USUARIO
                            </th>

                            <th>
                                ROL
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




                    <tbody id="usuariosTableBody">

                        <tr>

                            <td
                                colspan="5"
                                class="loading-users">

                                Cargando usuarios...

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>




            <div class="table-footer">

                <span id="usuariosMostrados">
                    Cargando usuarios...
                </span>


                <div class="pagination">

                    <button
                        type="button"
                        class="pagination-button"
                        id="paginaAnterior"
                        disabled>

                        ←

                    </button>


                    <button
                        type="button"
                        class="pagination-button active"
                        id="paginaActual">

                        1

                    </button>


                    <button
                        type="button"
                        class="pagination-button"
                        id="paginaSiguiente"
                        disabled>

                        →

                    </button>

                </div>

            </div>

        </div>




        <div
            class="user-modal"
            id="usuarioModal"
            hidden>

            <div class="user-modal-overlay"></div>


            <div
                class="user-modal-content"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modalUsuarioTitulo">




                <div class="user-modal-header">

                    <div>

                        <span class="section-label">
                            ADMINISTRACIÓN
                        </span>

                        <h3 id="modalUsuarioTitulo">
                            Nuevo usuario
                        </h3>

                    </div>


                    <button
                        type="button"
                        class="modal-close"
                        id="cerrarUsuarioModal"
                        aria-label="Cerrar">

                        ×

                    </button>

                </div>




                <form id="formUsuario">




                    <input
                        type="hidden"
                        id="usuarioId">




                    <div class="form-group">

                        <label for="usuarioNombres">
                            Nombres
                        </label>

                        <input
                            type="text"
                            id="usuarioNombres"
                            name="nombres"
                            placeholder="Ingrese los nombres"
                            maxlength="100"
                            required>

                    </div>




                    <div class="form-group">

                        <label for="usuarioApellidos">
                            Apellidos
                        </label>

                        <input
                            type="text"
                            id="usuarioApellidos"
                            name="apellidos"
                            placeholder="Ingrese los apellidos"
                            maxlength="100"
                            required>

                    </div>




                    <div class="form-group">

                        <label for="usuarioCorreo">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            id="usuarioCorreo"
                            name="correo"
                            placeholder="usuario@empresa.com"
                            maxlength="150"
                            required>

                    </div>




                    <div class="form-group">

                        <label for="usuarioPassword">
                            Contraseña
                        </label>

                        <input
                            type="password"
                            id="usuarioPassword"
                            name="password"
                            placeholder="Ingrese una contraseña"
                            minlength="6"
                            required>

                        <small>
                            La contraseña será almacenada de forma segura.
                        </small>

                    </div>




                    <div class="form-group">

                        <label for="usuarioRol">
                            Rol
                        </label>

                        <select
                            id="usuarioRol"
                            name="rol"
                            title="Seleccionar rol"
                            required>

                            <option value="usuario">
                                Usuario
                            </option>


                        </select>

                    </div>




                    <div class="form-group">

                        <label for="usuarioEstado">
                            Estado
                        </label>

                        <select
                            id="usuarioEstado"
                            name="estado"
                            title="Seleccionar estado"
                            required>

                            <option value="activo">
                                Activo
                            </option>

                            <option value="inactivo">
                                Inactivo
                            </option>

                        </select>

                    </div>




                    <div
                        id="usuarioFormMessage"
                        class="user-form-message"
                        aria-live="polite">
                    </div>




                    <div class="user-modal-actions">

                        <button
                            type="button"
                            class="modal-button secondary"
                            id="cancelarUsuario">

                            Cancelar

                        </button>


                        <button
                            type="submit"
                            class="modal-button primary"
                            id="guardarUsuario">

                            Crear usuario

                        </button>

                    </div>

                </form>

            </div>

        </div>


    </section>

    <script src="../scripts/usuarios.php?v=2"></script>

</body>

</html>
