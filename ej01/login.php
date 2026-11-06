<?php

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth.php';

iniciarSesion();

if (usuarioAutenticado()) {
    header('Location: index.php');
    exit;
}

$error = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($correo === '' || $contrasena === '') {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $sql = "SELECT id_usuario, nombre, correo, contrasena, id_rol
                FROM usuarios
                WHERE correo = ?";

        $consulta = $conexion->prepare($sql);
        $consulta->execute([$correo]);

        $usuarioEncontrado = $consulta->fetch();

        if (
            $usuarioEncontrado !== false &&
            password_verify(
                $contrasena,
                $usuarioEncontrado['contrasena']
            )
        ) {
            
            session_regenerate_id(true);

            $_SESSION['id_usuario'] =
                (int) $usuarioEncontrado['id_usuario'];
            
            $_SESSION['nombre'] =
                $usuarioEncontrado['nombre'];
            
            $_SESSION['id_rol'] =
                (int) $usuarioEncontrado['id_rol'];
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Gestor de Tareas</title>
</head>
<body>
    <h1>Gestor de Tareas</h1>
    <h2>Iniciar sesión</h2>
    <?php if (($_GET['registro'] ?? '') === 'exitoso'): ?>
        <p>Cuenta creada correctamente. Ya puedes iniciar sesión</p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p style="color: #A00;">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form action="login.php" method="POST">

        <label for="correo">Correo electrónico</label>
        <input
            type="email"
            id="correo"
            name="correo"
            value="<?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <label for="contrasena">Contraseña</label>
        <input
            type="password"
            id="contrasena"
            name="contrasena"
            required
        >

        <button type="submit">Iniciar sesión</button>
    
    </form>

    <p>
        ¿No tienes una cuenta?
        <a href="registro.php">Regístrate aquí</a>
    </p>
</body>
</html>