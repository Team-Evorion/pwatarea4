"use strict";

// ==========================================
// 1. ELEMENTOS DEL DASHBOARD
// ==========================================

const formulario = document.getElementById("formTarea");
const listaTareas = document.getElementById("listaTareas");
const mensaje = document.getElementById("mensaje");
const botonActualizar = document.getElementById("btnActualizar");

const totalTareas = document.getElementById("totalTareas");
const totalPendientes = document.getElementById("totalPendientes");
const totalCompletadas = document.getElementById("totalCompletadas");

// Archivo PHP desarrollado por Elkin.
const API_URL = "logica.php";

// ==========================================
// 2. COMUNICACIÓN CON EL BACKEND
// ==========================================

async function solicitarAPI(accion, datos = {}) {
    const respuesta = await fetch(API_URL, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        credentials: "same-origin",
        body: JSON.stringify({
            accion: accion,
            ...datos
        })
    });

    let resultado;

    try {
        resultado = await respuesta.json();
    } catch (error) {
        throw new Error("El servidor no devolvió una respuesta válida.");
    }

    if (respuesta.status === 401) {
        throw new Error("Tu sesión ha expirado. Inicia sesión nuevamente.");
    }

    if (!respuesta.ok || !resultado.ok) {
        throw new Error(
            resultado.error || "No se pudo completar la operación."
        );
    }

    return resultado;
}

// ==========================================
// 3. MOSTRAR MENSAJES
// ==========================================

function mostrarMensaje(texto, tipo = "exito") {
    mensaje.textContent = texto;
    mensaje.className = "mensaje";

    mensaje.classList.add(
        tipo === "error" ? "mensaje-error" : "mensaje-exito"
    );

    mensaje.hidden = false;
}

// ==========================================
// 4. ACTUALIZAR ESTADÍSTICAS
// ==========================================

function actualizarEstadisticas(tareas) {
    const pendientes = tareas.filter(
        tarea => tarea.estado === "pendiente"
    ).length;

    const completadas = tareas.filter(
        tarea => tarea.estado === "completada"
    ).length;

    totalTareas.textContent = tareas.length;
    totalPendientes.textContent = pendientes;
    totalCompletadas.textContent = completadas;
}

// ==========================================
// 5. CREAR BOTONES DE ACCIÓN
// ==========================================

function crearBoton(texto, clase, accion) {
    const boton = document.createElement("button");

    boton.type = "button";
    boton.textContent = texto;
    boton.className = clase;

    boton.addEventListener("click", accion);

    return boton;
}

// ==========================================
// 6. MOSTRAR LAS TAREAS EN PANTALLA
// ==========================================

function mostrarTareas(tareas) {
    listaTareas.replaceChildren();

    if (tareas.length === 0) {
        const vacio = document.createElement("p");

        vacio.className = "estado-vacio";
        vacio.textContent = ES_ADMIN
            ? "Todavía no existen tareas registradas."
            : "Todavía no tienes tareas. ¡Agrega la primera!";

        listaTareas.appendChild(vacio);
        return;
    }

    tareas.forEach(tarea => {
        const tarjeta = document.createElement("article");
        tarjeta.className = "tarea";

        const contenido = document.createElement("div");
        contenido.className = "tarea-contenido";

        const titulo = document.createElement("h3");
        titulo.textContent = tarea.titulo;

        const descripcion = document.createElement("p");
        descripcion.textContent =
            tarea.descripcion || "Sin descripción.";

        const estado = document.createElement("span");
        estado.className = "estado";

        if (tarea.estado === "completada") {
            estado.classList.add("estado-completada");
            estado.textContent = "Completada";
        } else {
            estado.classList.add("estado-pendiente");
            estado.textContent = "Pendiente";
        }

        contenido.append(titulo, descripcion, estado);

        // El administrador puede visualizar el autor.
        if (ES_ADMIN) {
            const autor = document.createElement("small");
            autor.className = "tarea-autor";
            autor.textContent = "Creada por: " + tarea.autor;
            contenido.appendChild(autor);
        }

        const acciones = document.createElement("div");
        acciones.className = "tarea-acciones";

        const textoBoton = tarea.estado === "completada"
            ? "Marcar pendiente"
            : "Completar";

        const botonEstado = crearBoton(
            textoBoton,
            "btn-estado",
            () => cambiarEstado(tarea.id_tarea, botonEstado)
        );

        acciones.appendChild(botonEstado);

        // Solo mostramos eliminar al administrador.
        if (ES_ADMIN) {
            const botonEliminar = crearBoton(
                "Eliminar",
                "btn-eliminar",
                () => eliminarTarea(tarea.id_tarea, botonEliminar)
            );

            acciones.appendChild(botonEliminar);
        }

        tarjeta.append(contenido, acciones);
        listaTareas.appendChild(tarjeta);
    });
}

// ==========================================
// 7. CONSULTAR TAREAS
// ==========================================

async function cargarTareas() {
    botonActualizar.disabled = true;

    try {
        const resultado = await solicitarAPI("listar");

        const tareas = resultado.tareas;

        if (!Array.isArray(tareas)) {
            throw new Error("La lista de tareas no es válida.");
        }

        mostrarTareas(tareas);
        actualizarEstadisticas(tareas);

    } catch (error) {
        mostrarMensaje(error.message, "error");

        listaTareas.replaceChildren();

        const aviso = document.createElement("p");
        aviso.className = "estado-vacio";
        aviso.textContent = "No se pudieron cargar las tareas.";

        listaTareas.appendChild(aviso);

    } finally {
        botonActualizar.disabled = false;
    }
}

// ==========================================
// 8. AGREGAR UNA NUEVA TAREA
// ==========================================

formulario.addEventListener("submit", async function(evento) {
    evento.preventDefault();

    const titulo = document.getElementById("titulo").value.trim();
    const descripcion = document.getElementById("descripcion").value.trim();

    if (!titulo) {
        mostrarMensaje("Debes escribir el título.", "error");
        return;
    }

    const botonGuardar = formulario.querySelector(
        'button[type="submit"]'
    );

    botonGuardar.disabled = true;

    try {
        const resultado = await solicitarAPI("agregar", {
            titulo: titulo,
            descripcion: descripcion
        });

        formulario.reset();
        mostrarMensaje(resultado.mensaje || "Tarea agregada.");
        await cargarTareas();

    } catch (error) {
        mostrarMensaje(error.message, "error");

    } finally {
        botonGuardar.disabled = false;
    }
});

// ==========================================
// 9. CAMBIAR ESTADO DE UNA TAREA
// ==========================================

async function cambiarEstado(idTarea, boton) {
    boton.disabled = true;

    try {
        const resultado = await solicitarAPI("cambiar_estado", {
            id_tarea: idTarea
        });

        mostrarMensaje(resultado.mensaje || "Estado actualizado.");
        await cargarTareas();

    } catch (error) {
        mostrarMensaje(error.message, "error");
        boton.disabled = false;
    }
}

// ==========================================
// 10. ELIMINAR UNA TAREA
// ==========================================

async function eliminarTarea(idTarea, boton) {
    if (!ES_ADMIN) {
        mostrarMensaje("No tienes permiso para eliminar.", "error");
        return;
    }

    const confirmar = window.confirm(
        "¿Estás seguro de que deseas eliminar esta tarea?"
    );

    if (!confirmar) {
        return;
    }

    boton.disabled = true;

    try {
        const resultado = await solicitarAPI("eliminar", {
            id_tarea: idTarea
        });

        mostrarMensaje(resultado.mensaje || "Tarea eliminada.");
        await cargarTareas();

    } catch (error) {
        mostrarMensaje(error.message, "error");
        boton.disabled = false;
    }
}

// ==========================================
// 11. BOTÓN ACTUALIZAR
// ==========================================

botonActualizar.addEventListener("click", cargarTareas);

// ==========================================
// 12. CARGA INICIAL
// ==========================================

cargarTareas();