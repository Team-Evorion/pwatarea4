<?php

require_once __DIR__ . '/auth.php';

protegerPagina();

$nombre = $_SESSION['nombre'];
$rol = esAdministrador()
    ? 'Administrador'
    : 'Usuario';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - Gestor de Tareas</title>
</head>
<body>

    <h1>Bienvenido al Gestor de Tareas</h1>

    <p>
        Usuario:
        <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <p>
        Rol:
        <?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <form action="logout.php" method="POST">
        <button type="submit">Cerrar sesión</button>
    </form>

</body>
</html>
