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

                    <div class="campo-contrasena">

                        <input
                            type="password"
                            name="contrasena"
                            id="contrasena"
                            placeholder="Ingresa tu contraseña"
                            required
                        >


                        <button
                            type="button"
                            class="boton-ver-contrasena"
                            aria-label="Mostrar contraseña"
                            aria-pressed="false">
                            
                            <!-- Ojo abierto -->
                            <svg class="icono-mostrar"
                                xmlns="http://www.w3.org/2000/svg"
                                width="22" height="22"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true">
                                <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            
                            <!-- Ojo tachado -->
                            <svg class="icono-ocultar"
                                xmlns="http://www.w3.org/2000/svg"
                                width="22" height="22"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                                hidden>
                                <path d="m3 3 18 18"/>
                                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                                <path d="M9.88 5.09A10.75 10.75 0 0 1 12 4.88c4.67 0 8.54 2.85 9.94 7.12a11.7 11.7 0 0 1-4.01 5.3"/>
                                <path d="M6.61 6.61A11.7 11.7 0 0 0 2.06 12c1.4 4.27 5.27 7.12 9.94 7.12 1.23 0 2.42-.2 3.5-.58"/>
                            </svg>
                        </button>
                    </div>
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

    <script>
        const botonVer = document.querySelector(".boton-ver-contrasena");
        const campo = document.querySelector("#contrasena");
        const iconoMostrar = botonVer.querySelector(".icono-mostrar");
        const iconoOcultar = botonVer.querySelector(".icono-ocultar");
        botonVer.addEventListener("click", () => {
            
            const estaVisible = campo.type === "text";
            campo.type = estaVisible ? "password" : "text";
            iconoMostrar.style.display = estaVisible ? "block" : "none";
            iconoOcultar.style.display = estaVisible ? "none" : "block";
            botonVer.setAttribute(
                "aria-label",
                estaVisible ? "Mostrar contraseña" : "Ocultar contraseña"
            );
            botonVer.setAttribute("aria-pressed", String(!estaVisible));
        });
    </script>
    
</body>
</html>