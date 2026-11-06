<?php

$host = 'localhost';
$baseDatos = 'pwatarea4';
$usuario = 'root';
$contrasena = '';

$dsn = "mysql:host=$host;dbname=$baseDatos;charset=utf8mb4";

try {
    $conexion = new PDO(
        $dsn,
        $usuario,
        $contrasena
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $conexion->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    $conexion->setAttribute(
        PDO::ATTR_EMULATE_PREPARES,
        false
    );
} catch (PDOException $error) {
    error_log($error->getMessage());

    http_response_code(500);
    exit('Error al conectar con la base de datos.');
}