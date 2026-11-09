<?php
/**
 * ============================================================================
 * TAREA PRÁCTICA 4 - PROGRAMACIÓN WEB AVANZADA
 * Archivo: logica.php
 * Hecho por: Elkin Velastegui
 * ============================================================================
 */

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth.php';

iniciarSesion();

// =========================================================================
// 1. FUNCIONES DE GESTIÓN DE TAREAS (Almacenamiento MySQL con PDO)
// =========================================================================

/**
 * Obtiene la lista de tareas desde MySQL.
 * - Administrador (rol 1): visualiza todas las tareas del sistema y su autor.
 * - Usuario (rol 2): visualiza únicamente sus propias tareas.
 */
function obtenerTareas(PDO $conexion, int $idUsuario, bool $esAdmin): array
{
    if ($esAdmin) {
        $sql = "SELECT t.id_tarea, t.titulo, t.descripcion, t.estado, t.id_usuario, u.nombre AS autor
                FROM tareas t
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                ORDER BY t.id_tarea DESC";
        $consulta = $conexion->prepare($sql);
        $consulta->execute();
    } else {
        $sql = "SELECT t.id_tarea, t.titulo, t.descripcion, t.estado, t.id_usuario, u.nombre AS autor
                FROM tareas t
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                WHERE t.id_usuario = ?
                ORDER BY t.id_tarea DESC";
        $consulta = $conexion->prepare($sql);
        $consulta->execute([$idUsuario]);
    }

    $tareas = $consulta->fetchAll();

    foreach ($tareas as &$tarea) {
        $tarea['es_propietario'] =
            (int) $tarea['id_usuario'] === $idUsuario;
    }

    unset($tarea);

    return $tareas;
}

/**
 * Obtiene los usuarios registrados para el administrador.
 */
function obtenerUsuarios(PDO $conexion): array
{
    $sql = "SELECT id_usuario, nombre
            FROM usuarios
            ORDER BY nombre ASC";

    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    return $consulta->fetchAll();
}

/**
 * Agrega una nueva tarea en la base de datos con estado 'pendiente' por defecto.
 */
function agregarTarea(PDO $conexion, string $titulo, ?string $descripcion, int $idUsuario): int|false
{
    $textoDescripcion = ($descripcion !== null && trim($descripcion) !== '')
        ? trim($descripcion)
        : null;

    $sql = "INSERT INTO tareas (titulo, descripcion, estado, id_usuario)
            VALUES (?, ?, 'pendiente', ?)";

    $consulta = $conexion->prepare($sql);
    $exito = $consulta->execute([$titulo, $textoDescripcion, $idUsuario]);

    return $exito ? (int) $conexion->lastInsertId() : false;
}

/**
 * Cambia el estado de una tarea a 'pendiente' o 'completada'.
 */
function cambiarEstadoTarea(PDO $conexion, int $idTarea, string $nuevoEstado, int $idUsuario): bool
{
    if (!in_array($nuevoEstado, ['pendiente', 'completada'], true)) {
        return false;
    }

    $sql = "UPDATE tareas
            SET estado = ?
            WHERE id_tarea = ? AND id_usuario = ?";

    $consulta = $conexion->prepare($sql);
    $consulta->execute([$nuevoEstado, $idTarea, $idUsuario]);

    return $consulta->rowCount() > 0;
}

/**
 * Alterna automáticamente el estado de una tarea ('pendiente' <-> 'completada').
 */
function alternarEstadoTarea(PDO $conexion, int $idTarea, int $idUsuario): ?string
{
    $sql = "SELECT estado
            FROM tareas
            WHERE id_tarea = ? AND id_usuario = ?";

    $consulta = $conexion->prepare($sql);
    $consulta->execute([$idTarea, $idUsuario]);
    $tarea = $consulta->fetch();

    if ($tarea === false) {
        return null;
    }

    $nuevoEstado = ($tarea['estado'] === 'completada') ? 'pendiente' : 'completada';

    $sqlUpdate = "UPDATE tareas
                    SET estado = ?
                    WHERE id_tarea = ? AND id_usuario = ?";

    $update = $conexion->prepare($sqlUpdate);
    $update->execute([$nuevoEstado, $idTarea, $idUsuario]);

    return $nuevoEstado;
}

/**
 * Elimina una tarea de la base de datos.
 * - Cada usuario puede eliminar únicamente sus propias tareas.
 */
function eliminarTarea(PDO $conexion, int $idTarea, int $idUsuario): bool
{
    $sql = "DELETE FROM tareas
            WHERE id_tarea = ? AND id_usuario = ?";

    $consulta = $conexion->prepare($sql);
    $consulta->execute([$idTarea, $idUsuario]);

    return $consulta->rowCount() > 0;
}

// =========================================================================
// 2. CONTROLADOR INTERACTIVO (Recibe peticiones de JS Fetch o Formularios)
// =========================================================================

if (basename($_SERVER['SCRIPT_FILENAME']) === 'logica.php') {
    header('Content-Type: application/json; charset=utf-8');

    if (!usuarioAutenticado()) {
        http_response_code(401);
        echo json_encode([
            'ok' => false,
            'error' => 'Debes iniciar sesión para realizar esta acción.'
        ]);
        exit;
    }

    $idUsuario = obtenerUsuarioId();
    $esAdmin   = esAdministrador();

    // Soporta datos enviados en JSON por fetch() o por $_POST de formularios
    $entradaJson = json_decode(file_get_contents('php://input'), true);
    $datos  = is_array($entradaJson) ? $entradaJson : $_POST;
    $accion = $datos['accion'] ?? ($_GET['accion'] ?? 'listar');

    try {
        switch ($accion) {
            case 'listar':
                $tareas = obtenerTareas($conexion, $idUsuario, $esAdmin);
                echo json_encode([
                    'ok' => true,
                    'id_usuario' => $idUsuario,
                    'id_rol' => obtenerRol(),
                    'es_admin' => $esAdmin,
                    'tareas' => $tareas,
                    'usuarios' => $esAdmin ? obtenerUsuarios($conexion) : []
                ]);
                break;

            case 'agregar':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    http_response_code(405);
                    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
                    break;
                }

                $titulo = trim($datos['titulo'] ?? '');
                $descripcion = trim($datos['descripcion'] ?? '');

                if ($titulo === '') {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'El título de la tarea es obligatorio.']);
                    break;
                }

                if (strlen($titulo) > 150) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'El título no puede superar los 150 caracteres.']);
                    break;
                }

                $nuevoId = agregarTarea($conexion, $titulo, $descripcion, $idUsuario);

                if ($nuevoId !== false) {
                    echo json_encode([
                        'ok' => true,
                        'mensaje' => 'Tarea agregada correctamente.',
                        'id_tarea' => $nuevoId
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar la tarea.']);
                }
                break;

            case 'completar':
            case 'cambiar_estado':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    http_response_code(405);
                    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
                    break;
                }

                $idTarea = (int) ($datos['id_tarea'] ?? 0);

                if ($idTarea <= 0) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'ID de tarea inválido.']);
                    break;
                }

                if (isset($datos['estado']) && in_array($datos['estado'], ['pendiente', 'completada'], true)) {
                    $exito = cambiarEstadoTarea($conexion, $idTarea, $datos['estado'], $idUsuario);
                    $estadoFinal = $datos['estado'];
                } else {
                    $estadoFinal = alternarEstadoTarea($conexion, $idTarea, $idUsuario);
                    $exito = ($estadoFinal !== null);
                }

                if ($exito) {
                    echo json_encode([
                        'ok' => true,
                        'mensaje' => 'Estado de la tarea actualizado.',
                        'estado' => $estadoFinal
                    ]);
                } else {
                    http_response_code(403);
                    echo json_encode([
                        'ok' => false,
                        'error' => 'No tienes permiso para modificar esta tarea o no existe.'
                    ]);
                }
                break;

            case 'eliminar':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    http_response_code(405);
                    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
                    break;
                }

                $idTarea = (int) ($datos['id_tarea'] ?? 0);

                if ($idTarea <= 0) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'ID de tarea inválido.']);
                    break;
                }

                if (eliminarTarea($conexion, $idTarea, $idUsuario)) {
                    echo json_encode([
                        'ok' => true,
                        'mensaje' => 'Tarea eliminada correctamente.'
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'ok' => false,
                        'error' => 'La tarea no existe o no tienes permiso para eliminarla.'
                    ]);
                }
                break;

            default:
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);
                break;
        }
    } catch (PDOException $errorBD) {
        error_log($errorBD->getMessage());
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Error al procesar la operación en la base de datos.']);
    }
}