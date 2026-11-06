const tarjetas = document.querySelectorAll(
    ".tarjeta-caracteristica, .tarjeta-integrante"
);

const movimientoReducido = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
).matches;

if (!movimientoReducido && "IntersectionObserver" in window) {
    
    const observador = new IntersectionObserver((entradas) => {
        entradas.forEach((entrada) => {
            if (entrada.isIntersecting) {
                entrada.target.classList.add("visible");
                entrada.target.classList.remove("por-aparecer");
                observador.unobserve(entrada.target);
            }
        });
    }, {
        threshold: 0.15
    });
    
    tarjetas.forEach((tarjeta) => {
        tarjeta.classList.add("por-aparecer");
        observador.observe(tarjeta);
    });
}


const encabezado = document.querySelector(".encabezado");
const botonInicio = document.querySelector("#volver-inicio");

let ultimaPosicion = window.scrollY;

window.addEventListener("scroll", () => {

    const posicionActual = window.scrollY;

    if (window.innerWidth <= 600) {

        botonInicio.classList.toggle(
            "mostrar",
            posicionActual > 200
        );

        encabezado.classList.remove("oculto");

    } else {

        botonInicio.classList.remove("mostrar");

        if (posicionActual <= 80) {
            encabezado.classList.remove("oculto");
        } else if (posicionActual > ultimaPosicion + 3) {
            encabezado.classList.add("oculto");
        } else if (posicionActual < ultimaPosicion - 3) {
            encabezado.classList.remove("oculto");
        }

    }

    ultimaPosicion = posicionActual;
});

document.addEventListener("mousemove", (evento) => {

    if (window.innerWidth > 600 && evento.clientY <= 20) {
        encabezado.classList.remove("oculto");
    }

});

encabezado.addEventListener("focusin", () => {
    encabezado.classList.remove("oculto");
});

botonInicio.addEventListener("click", () => {

    window.scrollTo({
        top: 0,
        behavior: movimientoReducido ? "instant" : "smooth"
    });

});

window.addEventListener("resize", () => {
    if (window.innerWidth <= 600) {
        encabezado.classList.remove("oculto");
    }

    botonInicio.classList.toggle(
        "mostrar",
        window.innerWidth <= 600 && window.scrollY > 200
    );
});

botonInicio.classList.toggle(
    "mostrar",
    window.innerWidth <= 600 && window.scrollY > 200
);