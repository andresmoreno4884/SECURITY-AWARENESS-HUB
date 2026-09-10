<?php require_once __DIR__ . '/../includes/page_context.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Asignaciones | Security Awareness Hub</title>


    <link rel="stylesheet" href="css/asignaciones.css">
</head>

<body>



    <section class="admin-view">



        <div class="dashboard-heading">

            <div>

                <span class="section-label">
                    GESTIÓN DE CAPACITACIÓN
                </span>

                <h2>
                    Asignaciones
                </h2>

                <p>
                    Asigna cursos y evaluaciones a los usuarios
                    de la plataforma.
                </p>

            </div>

            <div class="admin-actions">

                <button
                    type="button"
                    class="admin-button"
                    id="btnNuevaAsignacion">

                    + Nueva asignación

                </button>

            </div>

        </div>




        <div class="dashboard-cards">



            <div class="dashboard-card">

                <div class="card-icon">
                    ✓
                </div>

                <div>

                    <span>
                        ASIGNACIONES
                    </span>

                    <strong id="totalAsignaciones">
                        0
                    </strong>

                    <small>
                        Asignaciones registradas
                    </small>

                </div>

            </div>




            <div class="dashboard-card">

                <div class="card-icon">
                    ◉
                </div>

                <div>

                    <span>
                        ACTIVAS
                    </span>

                    <strong id="asignacionesActivas">
                        0
                    </strong>

                    <small>
                        Asignaciones activas
                    </small>

                </div>

            </div>




            <div class="dashboard-card">

                <div class="card-icon">
                    ★
                </div>

                <div>

                    <span>
                        COMPLETADAS
                    </span>

                    <strong id="asignacionesCompletadas">
                        0
                    </strong>

                    <small>
                        Capacitaciones completadas
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
                    id="buscarAsignacion"
                    placeholder="Buscar usuario o curso..."
                    autocomplete="off">

            </div>




            <select
                class="users-filter"
                id="filtroEstadoAsignacion"
                title="Filtrar por estado">

                <option value="todos">
                    Todos los estados
                </option>

                <option value="pendiente">
                    Pendientes
                </option>

                <option value="progreso">
                    En progreso
                </option>

                <option value="completado">
                    Completadas
                </option>

            </select>




            <select
                class="users-filter"
                id="filtroTipoAsignacion"
                title="Filtrar por tipo">

                <option value="todos">
                    Todos los tipos
                </option>

                <option value="curso">
                    Cursos
                </option>

                <option value="evaluacion">
                    Evaluaciones
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
                        Asignaciones registradas
                    </h3>

                </div>

                <span
                    class="card-period"
                    id="contadorAsignaciones">

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
                                CURSO
                            </th>

                            <th>
                                TIPO
                            </th>

                            <th>
                                ESTADO
                            </th>

                            <th>
                                FECHA DE ASIGNACIÓN
                            </th>

                            <th>
                                ACCIONES
                            </th>

                        </tr>

                    </thead>




                    <tbody id="asignacionesTableBody">

                        <tr>

                            <td
                                colspan="6"
                                class="loading-users">

                                Cargando asignaciones...

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>




            <div class="table-footer">

                <span id="asignacionesMostradas">

                    Cargando asignaciones...

                </span>


                <div class="pagination">

                    <button
                        type="button"
                        class="pagination-button"
                        id="paginaAnteriorAsignaciones"
                        disabled>

                        ←

                    </button>


                    <button
                        type="button"
                        class="pagination-button active"
                        id="paginaActualAsignaciones">

                        1

                    </button>


                    <button
                        type="button"
                        class="pagination-button"
                        id="paginaSiguienteAsignaciones"
                        disabled>

                        →

                    </button>

                </div>

            </div>

        </div>




        <div
            class="user-modal"
            id="asignacionModal"
            hidden>



            <div
                class="user-modal-overlay"
                id="asignacionModalOverlay">
            </div>




            <div
                class="user-modal-content"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modalAsignacionTitulo">




                <div class="user-modal-header">

                    <div>

                        <span class="section-label">
                            ADMINISTRACIÓN
                        </span>

                        <h3 id="modalAsignacionTitulo">
                            Nueva asignación
                        </h3>

                    </div>


                    <button
                        type="button"
                        class="modal-close"
                        id="cerrarAsignacionModal"
                        aria-label="Cerrar">

                        ×

                    </button>

                </div>




                <form id="formAsignacion">



                    <input
                        type="hidden"
                        id="asignacionId">




                    <div class="form-group">

                        <label for="asignacionUsuario">
                            Usuario
                        </label>

                        <select
                            id="asignacionUsuario"
                            name="usuario_id"
                            title="Seleccionar usuario"
                            required>

                            <option value="">
                                Seleccione un usuario
                            </option>

                        </select>

                    </div>




                    <div class="form-group">

                        <label for="asignacionCurso">
                            Curso
                        </label>

                        <select
                            id="asignacionCurso"
                            name="curso_id"
                            title="Seleccionar curso"
                            required>

                            <option value="">
                                Seleccione un curso
                            </option>

                        </select>

                    </div>

                    <div class="form-group">

                        <label for="asignacionEvaluacion">
                            Evaluación
                        </label>

                        <select
                            id="asignacionEvaluacion"
                            name="evaluacion_id"
                            title="Seleccionar evaluación">

                            <option value="">
                                Seleccione una evaluación
                            </option>

                        </select>

                    </div>




                    <div class="form-group">

                        <label for="asignacionTipo">
                            Tipo de asignación
                        </label>

                        <select
                            id="asignacionTipo"
                            name="tipo"
                            title="Seleccionar tipo de asignación"
                            required>

                            <option value="curso">
                                Curso
                            </option>

                            <option value="evaluacion">
                                Evaluación
                            </option>

                        </select>

                    </div>




                    <div class="form-group">

                        <label for="asignacionFecha">
                            Fecha de asignación
                        </label>

                        <input
                            type="date"
                            id="asignacionFecha"
                            name="fecha_asignacion"
                            required>

                    </div>




                    <div class="form-group">

                        <label for="asignacionEstado">
                            Estado
                        </label>

                        <select
                            id="asignacionEstado"
                            name="estado"
                            title="Seleccionar estado"
                            required>

                            <option value="pendiente">
                                Pendiente
                            </option>

                            <option value="progreso">
                                En progreso
                            </option>

                            <option value="completado">
                                Completado
                            </option>

                        </select>

                    </div>




                    <div
                        id="asignacionFormMessage"
                        class="user-form-message"
                        aria-live="polite">
                    </div>




                    <div class="user-modal-actions">

                        <button
                            type="button"
                            class="modal-button secondary"
                            id="cancelarAsignacion">

                            Cancelar

                        </button>


                        <button
                            type="submit"
                            class="modal-button primary"
                            id="guardarAsignacion">

                            Crear asignación

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </section>


</body>

</html>
