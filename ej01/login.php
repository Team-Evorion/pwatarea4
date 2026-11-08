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

    <title>Iniciar sesión | Gestor de Tareas</title>

    <link rel="stylesheet" href="estilos.css">
</head>

<body class="pagina-auth">

    <main class="contenedor-auth">

        <section class="tarjeta-auth">

            <div class="auth-encabezado">
                <div class="auth-icono">✓</div>

                <h1>Gestor de Tareas</h1>
                <p>Organiza tus actividades de forma sencilla.</p>
            </div>

            <div class="auth-titulo">
                <h2>Iniciar sesión</h2>
                <p>Ingresa tus credenciales para continuar.</p>
            </div>

            <?php if (($_GET['registro'] ?? '') === 'exitoso'): ?>
                <div class="mensaje mensaje-exito" role="status">
                    Cuenta creada correctamente.
                    Ya puedes iniciar sesión.
                </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="mensaje mensaje-error" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">

                <div class="campo">
                    <label for="correo">Correo electrónico</label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="ejemplo@correo.com"
                        autocomplete="email"
                        value="<?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="contrasena">Contraseña</label>

                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-principal btn-auth">
                    Iniciar sesión
                </button>

            </form>

            <p class="auth-enlace">
                ¿No tienes una cuenta?
                <a href="registro.php">Regístrate aquí</a>
            </p>

        </section>

        <p class="auth-pie">
            Gestor de Tareas · Programación Web Avanzada
        </p>

    </main>

</body>
</html>