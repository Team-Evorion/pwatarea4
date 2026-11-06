<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestor de Tareas | Inicio</title>
        <link rel="stylesheet" href="recursos/principal.css">
        <script src="recursos/principal.js" defer></script>
    </head>
    
    <body>
        <header class="encabezado">
            <nav class="navegacion" id="menu-principal">
                
                <a href="index.php" class="logo">
                    Gestor de Tareas
                </a>
                
                <div class="enlaces-nav">
                    <a href="index.php">Inicio</a>
                    <a href="#caracteristicas">Características</a>
                    <a href="#integrantes">Equipo</a>
                    <a href="ej01/login.php">Iniciar sesión</a>
                </div>
            </nav>
        </header>
        
        <button
            type="button"
            class="boton-navegacion"
            id="volver-inicio"
            aria-label="Volver al inicio"
            title="Volver al inicio">
            &uarr;
        </button>
        
        <main>
            <section class="hero">
                
                <div class="hero-contenido">
                    
                    <span class="etiqueta">GESTIÓN DE TAREAS</span>
                    
                    <h1>Organiza tus tareas, simplifica tu día.</h1>
                    
                    <p>
                        Una plataforma web para gestionar tus actividades de manera sencilla, organizada y segura.
                    </p>
                
                    <div class="hero-botones">
                        
                        <a href="ej01/login.php" class="btn btn-primario">Iniciar sesión</a>
                        
                        <a href="ej01/registro.php" class="btn btn-secundario">Crear cuenta</a>
                        
                    </div>
                    
                </div>
                
                <div class="hero-visual">
                    
                    <div class="tarjeta-tareas">
                        
                        <h2>Mis tareas</h2>
                        
                        <ul class="lista-demo">
                            <!-- Cantidad ilustrativa -->
                            <li>Estudiar programación</li>
                            <li>Completar actividades</li>
                            <li>Revisar el proyecto</li>
                            
                        </ul>
                        
                    </div>
                    
                </div>
                
            </section>
            
            <section class="caracteristicas" id="caracteristicas">
                <h2>¿Qué puedes hacer con nuestro sistema?</h2>
                
                <div class="contenedor-caracteristicas">
                    
                    <article class="tarjeta-caracteristica">
                        <h3>Organizar tareas</h3>
                        <p>
                            Agrega y visualiza tus actividades en un solo lugar.
                        </p>
                    </article>
                    
                    <article class="tarjeta-caracteristica">
                        <h3>Gestionar avances</h3>
                        <p>
                            Marca las tareas completadas y elimina las que ya no necesites.
                        </p>
                    </article>
                    
                    <article class="tarjeta-caracteristica">
                        <h3>Acceso por roles</h3>
                        <p>
                            Utiliza el sistema con permisos de administrador o usuario.
                        </p>
                    </article>
                </div>
            
            </section>
            <section class="integrantes" id="integrantes">
                <h2>Equipo de desarrollo</h2>
                
                <div class="contenedor-integrantes">
                    <article class="tarjeta-integrante">
                        <h3>Jesús Rafael Fernández Pérez</h3>
                    </article>
                    
                    <article class="tarjeta-integrante">
                        <h3>José Andrés Merlin Gamboa</h3>
                    </article>
                    
                    <article class="tarjeta-integrante">
                        <h3>Terry Steven Ponce Farías</h3>
                    </article>
                    
                    <article class="tarjeta-integrante">
                        <h3>José Alberto Rosado Dominguez</h3>
                    </article>
                    
                    <article class="tarjeta-integrante">
                        <h3>Elkin Daniel Velastegí Zambrano</h3>
                    </article>
                    
                    <article class="tarjeta-integrante">
                        <h3>Zahid Joel Zambrano Moreira</h3>
                    </article>
                </div>
            </section>
        </main>
            
            <footer>
                <p>Universidad Bolivariana del Ecuador</p>
                <p>Programación Web Avanzada - Tarea Práctica 4</p>
            </footer>
    </body>
</html>