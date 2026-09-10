<?php require_once __DIR__ . '/../includes/page_context.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Cursos | Security Awareness Hub
    </title>


    <link
        rel="stylesheet"
        href="css/courses_admin.css">

</head>

<body>



    <section class="courses-panel">




        <div class="dashboard-heading">

            <div>

                <span class="section-label">
                    GESTIÓN DE CURSOS
                </span>

                <h2>
                    Cursos
                </h2>

                <p>
                    Administra los cursos de capacitación en
                    seguridad digital disponibles en la plataforma.
                </p>

            </div>


            <button
                type="button"
                class="admin-button"
                id="btnNuevoCurso">

                + Nuevo curso

            </button>

        </div>




        <div class="dashboard-cards">




            <div class="dashboard-card">

                <div class="card-icon">
                    ▣
                </div>

                <div>

                    <span>
                        CURSOS
                    </span>

                    <strong id="totalCursos">
                        0
                    </strong>

                    <small>
                        Cursos registrados
                    </small>

                </div>

            </div>




            <div class="dashboard-card">

                <div class="card-icon">
                    ✓
                </div>

                <div>

                    <span>
                        ACTIVOS
                    </span>

                    <strong id="cursosActivos">
                        0
                    </strong>

                    <small>
                        Cursos disponibles
                    </small>

                </div>

            </div>




            <div class="dashboard-card">

                <div class="card-icon">
                    ◉
                </div>

                <div>

                    <span>
                        INACTIVOS
                    </span>

                    <strong id="cursosInactivos">
                        0
                    </strong>

                    <small>
                        Cursos deshabilitados
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
                    id="buscarCurso"
                    placeholder="Buscar por nombre del curso..."
                    autocomplete="off">

            </div>




            <select
                class="users-filter"
                id="filtroEstadoCurso"
                title="Filtrar cursos por estado">

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
                id="filtroNivelCurso"
                title="Filtrar cursos por nivel">

                <option value="todos">
                    Todos los niveles
                </option>

                <option value="basico">
                    Básico
                </option>

                <option value="intermedio">
                    Intermedio
                </option>

                <option value="avanzado">
                    Avanzado
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
                        Cursos registrados
                    </h3>

                </div>


                <span
                    class="card-period"
                    id="contadorCursos">

                    Cargando...

                </span>

            </div>




            <div class="table-wrapper">

                <table class="users-table courses-table">

                    <thead>

                        <tr>

                            <th>
                                CURSO
                            </th>

                            <th>
                                DESCRIPCIÓN
                            </th>

                            <th>
                                NIVEL
                            </th>

                            <th>
                                DURACIÓN
                            </th>

                            <th>
                                MÓDULOS
                            </th>

                            <th>
                                APROBACIÓN
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




                    <tbody id="cursosTableBody">

                        <tr>

                            <td
                                colspan="9"
                                class="loading-users">

                                Cargando cursos...

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>




            <div class="table-footer">

                <span id="cursosMostrados">
                    Cargando cursos...
                </span>


                <div class="pagination">

                    <button
                        type="button"
                        class="pagination-button"
                        id="paginaAnteriorCurso"
                        disabled>

                        ←

                    </button>


                    <button
                        type="button"
                        class="pagination-button active"
                        id="paginaActualCurso">

                        1

                    </button>


                    <button
                        type="button"
                        class="pagination-button"
                        id="paginaSiguienteCurso"
                        disabled>

                        →

                    </button>

                </div>

            </div>

        </div>




        <div
            class="user-modal"
            id="cursoModal"
            hidden>

            <div class="user-modal-overlay"></div>


            <div
                class="user-modal-content"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modalCursoTitulo">




                <div class="user-modal-header">

                    <div>

                        <span class="section-label">
                            ADMINISTRACIÓN
                        </span>

                        <h3 id="modalCursoTitulo">
                            Nuevo curso
                        </h3>

                    </div>


                    <button
                        type="button"
                        class="modal-close"
                        id="cerrarCursoModal"
                        aria-label="Cerrar">

                        ×

                    </button>

                </div>




                <form id="formCurso">




                    <input
                        type="hidden"
                        id="cursoId">




                    <div class="form-group">

                        <label for="cursoNombre">
                            Nombre del curso
                        </label>

                        <input
                            type="text"
                            id="cursoNombre"
                            name="nombre"
                            placeholder="Ingrese el nombre del curso"
                            maxlength="150"
                            required>

                    </div>




                    <div class="form-group">

                        <label for="cursoDescripcion">
                            Descripción
                        </label>

                        <textarea
                            id="cursoDescripcion"
                            name="descripcion"
                            placeholder="Ingrese una descripción del curso"
                            maxlength="500"
                            rows="4"></textarea>

                    </div>




                    <div class="form-group">

                        <label for="cursoNivel">
                            Nivel de dificultad
                        </label>

                        <select
                            id="cursoNivel"
                            name="nivel_dificultad"
                            title="Seleccionar nivel del curso"
                            required>

                            <option value="">
                                Seleccione un nivel
                            </option>

                            <option value="basico">
                                Básico
                            </option>

                            <option value="intermedio">
                                Intermedio
                            </option>

                            <option value="avanzado">
                                Avanzado
                            </option>

                        </select>

                    </div>




                    <div class="form-group">

                        <label for="cursoDuracion">
                            Duración
                        </label>

                        <input
                            type="number"
                            id="cursoDuracion"
                            name="duracion"
                            placeholder="Duración en horas"
                            min="1"
                            max="999"
                            required>

                    </div>




                    <div class="form-group">

                        <label for="cursoModulos">
                            Número de módulos
                        </label>

                        <input
                            type="number"
                            id="cursoModulos"
                            name="numero_modulos"
                            placeholder="Ejemplo: 5"
                            min="1"
                            max="999"
                            required>

                    </div>




                    <div class="form-group">

                        <label for="cursoAprobacion">
                            Porcentaje de aprobación
                        </label>

                        <input
                            type="number"
                            id="cursoAprobacion"
                            name="porcentaje_aprobacion"
                            placeholder="Ejemplo: 70"
                            min="1"
                            max="100"
                            required>

                        <small>
                            Porcentaje mínimo necesario para aprobar el curso.
                        </small>

                    </div>




                    <div class="form-group">

                        <label for="cursoEstado">
                            Estado
                        </label>

                        <select
                            id="cursoEstado"
                            name="estado"
                            title="Seleccionar estado del curso"
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
                        id="cursoFormMessage"
                        class="user-form-message"
                        aria-live="polite">
                    </div>




                    <div class="admin-modal-actions">

    <button
        type="button"
        id="cancelarCurso"
        class="admin-secondary-button">
        Cancelar
    </button>

    <button
        type="submit"
        id="guardarCurso"
        class="admin-primary-button">
        Crear curso
    </button>

</div>

                </form>

            </div>

        </div>


    </section>

</body>

</html>
