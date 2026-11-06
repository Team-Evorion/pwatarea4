<?php

function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}


function usuarioAutenticado(): bool
{
    iniciarSesion();

    return isset($_SESSION['id_usuario']);
}


function obtenerUsuarioId(): ?int
{
    if (!usuarioAutenticado()) {
        return null;
    }

    return (int) $_SESSION['id_usuario'];
}


function obtenerRol(): ?int
{
    if (!usuarioAutenticado()) {
        return null;
    }

    return isset($_SESSION['id_rol'])
    ? (int) $_SESSION['id_rol']
    : null;
}


function esAdministrador(): bool
{
    return obtenerRol() === 1;
}


function protegerPagina(): void
{
    if (!usuarioAutenticado()) {
        header('Location: login.php');
        exit;
    }
}