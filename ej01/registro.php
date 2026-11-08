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

    <title>Crear cuenta | Gestor de Tareas</title>

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
                <h2>Crear cuenta</h2>
                <p>Completa tus datos para comenzar.</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="mensaje mensaje-error" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="registro.php" method="POST">

                <div class="campo">
                    <label for="nombre">Nombre completo</label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        maxlength="100"
                        placeholder="Ingresa tu nombre"
                        autocomplete="name"
                        value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="correo">Correo electrónico</label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        maxlength="100"
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
                        minlength="8"
                        placeholder="Crea una contraseña segura"
                        autocomplete="new-password"
                        aria-describedby="ayuda-contrasena"
                        required
                    >

                    <small id="ayuda-contrasena" class="ayuda-campo">
                        Mínimo 8 caracteres, una mayúscula,
                        una minúscula y un número.
                    </small>
                </div>

                <div class="campo">
                    <label for="confirmar">Confirmar contraseña</label>

                    <input
                        type="password"
                        id="confirmar"
                        name="confirmar"
                        placeholder="Repite tu contraseña"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-principal btn-auth">
                    Crear cuenta
                </button>

            </form>

            <p class="auth-enlace">
                ¿Ya tienes una cuenta?
                <a href="login.php">Iniciar sesión</a>
            </p>

        </section>

        <p class="auth-pie">
            Gestor de Tareas · Programación Web Avanzada
        </p>

    </main>

</body>
</html>