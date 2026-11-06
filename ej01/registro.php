<?php

require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/auth.php";

iniciarSesion();

if (usuarioAutenticado()) {
    header('Location: index.php');
    exit;
}

$error = '';
$nombre = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (
        $nombre === '' ||
        $correo === '' ||
        $contrasena === '' ||
        $confirmar === ''
    ) {
        $error = 'Todos los campos son obligatorios';
    
    } elseif (strlen($nombre) > 100) {
        $error = 'El nombre es demasiado largo.';
    
    } elseif (
        strlen($correo) > 100 ||
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
    ) {
        $error = 'El correo electrónico no es válido.';
    
    } elseif (
        strlen($contrasena) < 8 ||
        !preg_match('/[A-Z]/', $contrasena) ||
        !preg_match('/[a-z]/', $contrasena) ||
        !preg_match('/[0-9]/', $contrasena)
    ) {
        $error = 'La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula y un número.';
    
    } elseif ($contrasena !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    
    } else {
        $sql = "SELECT id_usuario
                FROM usuarios
                Where correo = ?";

        $consulta = $conexion->prepare($sql);
        $consulta->execute([$correo]);

        $usuarioExistente = $consulta->fetch();

        if ($usuarioExistente !== false) {
            $error = 'No se pudo completar el registro con ese correo.';
        
        } else {
            $hash = password_hash(
                $contrasena,
                PASSWORD_DEFAULT
            );

            $sql = "INSERT INTO usuarios
                    (nombre, correo, contrasena, id_rol)
                    VALUES (?, ?, ?, 2)";
            
            $consulta = $conexion->prepare($sql);

            try {
                $consulta->execute([
                    $nombre,
                    $correo,
                    $hash
                ]);

                header('Location: login.php?registro=exitoso');
                exit;
            } catch (PDOException $errorBD) {
                error_log($errorBD->getMessage());
                $error = 'No se pudo completar el registro.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Gestor de Tareas</title>
</head>
<body>
    <h1>Gestor de Tareas</h1>
    <h2>Crear cuenta</h2>

    <?php if ($error !== ''): ?>
        <p style="color: #A00">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form action="registro.php" method="POST">
        <label for="nombre">Nombre</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="100"
            value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <label for="correo">Correo electrónico</label>
        <input
            type="email"
            id="correo"
            name="correo"
            maxlength="100"
            value="<?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <label for="contrasena">Contraseña</label>
        <input
            type="password"
            id="contrasena"
            name="contrasena"
            minlength="8"
            required
        >

        <label for="confirmar">Confirmar contraseña</label>
        <input
            type="password"
            id="confirmar"
            name="confirmar"
            required
        >

        <button type="submit">Crear cuenta</button>

    </form>

    <p>
        ¿Ya tienes una cuenta?
        <a href="login.php">Iniciar sesión</a>
    </p>
</body>
</html>