<<?php
require_once __DIR__ . '/auth.php';

// Impide acceder sin iniciar sesión.
protegerPagina();

// Información del usuario autenticado.
$nombre = $_SESSION['nombre'] ?? 'Usuario';
$esAdmin = esAdministrador();
$rol = $esAdmin ? 'Administrador' : 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Gestor de Tareas</title>

    <!-- Hoja de estilos del dashboard -->
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

    <!-- ENCABEZADO PRINCIPAL -->
    <header class="encabezado">
        <div class="marca">
            <h1>Gestor de Tareas</h1>
            <p>Organiza tus actividades de manera sencilla.</p>
        </div>

        <div class="perfil">
            <div class="datos-usuario">
                <strong>
                    <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
                </strong>

                <span class="etiqueta-rol">
                    <?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <form action="logout.php" method="POST">
                <button type="submit" class="btn-salir">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </header>

    <main class="contenedor">

        <!-- RESUMEN DE TAREAS -->
        <section class="resumen">
            <article class="tarjeta-estadistica">
                <h2>Total de tareas</h2>
                <p id="totalTareas">0</p>
            </article>

            <article class="tarjeta-estadistica">
                <h2>Pendientes</h2>
                <p id="totalPendientes">0</p>
            </article>

            <article class="tarjeta-estadistica">
                <h2>Completadas</h2>
                <p id="totalCompletadas">0</p>
            </article>
        </section>

        <!-- FORMULARIO PARA CREAR TAREAS -->
        <section class="panel">
            <h2>Agregar nueva tarea</h2>
            <p>Registra una actividad que necesites realizar.</p>

            <form id="formTarea">
                <div class="campo">
                    <label for="titulo">Título de la tarea</label>

                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        maxlength="150"
                        placeholder="Ejemplo: Preparar exposición"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="descripcion">Descripción</label>

                    <textarea
                        id="descripcion"
                        name="descripcion"
                        rows="4"
                        placeholder="Describe tu tarea..."
                    ></textarea>
                </div>

                <button type="submit" class="btn-principal">
                    Agregar tarea
                </button>
            </form>
        </section>

        <!-- MENSAJES DE ÉXITO O ERROR -->
        <div
            id="mensaje"
            class="mensaje"
            role="status"
            aria-live="polite"
            hidden
        ></div>

        <!-- LISTADO DE TAREAS -->
        <section class="panel">
            <div class="titulo-seccion">
                <h2>
                    <?= $esAdmin ? 'Todas las tareas' : 'Mis tareas' ?>
                </h2>

                <button
                    type="button"
                    id="btnActualizar"
                    class="btn-secundario"
                >
                    Actualizar
                </button>
            </div>

            <div id="listaTareas" class="lista-tareas">
                <p>Cargando tareas...</p>
            </div>
        </section>

    </main>

    <!-- Información de rol para JavaScript -->
    <script>
        const ES_ADMIN = <?= json_encode($esAdmin) ?>;
    </script>

    <!-- Archivo JavaScript -->
    <script src="tareas.js" defer></script>

</body>
</html>
