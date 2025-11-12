class Memoria {
    #tablero_bloqueado;
    #primera_carta;
    #segunda_carta;
    constructor() {
        this.#tablero_bloqueado = false;
        this.#primera_carta = null;
        this.#segunda_carta = null;

        this.#barajarCartas();

        this.cronometro = new Cronometro();
        this.cronometro.arrancar();
    }

    #barajarCartas() {
        const main = document.querySelector("main");
        const cartas = Array.from(main.getElementsByTagName("article"));
    
        for (let i = cartas.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [cartas[i], cartas[j]] = [cartas[j], cartas[i]];
        }
    
        cartas.forEach(carta => {
            main.appendChild(carta);
            carta.addEventListener("click", () => this.voltearCarta(carta));
        });
    }

    #reiniciarAtributos() {
        this.#tablero_bloqueado = false;
        this.#primera_carta = null;
        this.#segunda_carta = null;
    }

    #comprobarJuego() {
        var cartas = document.getElementsByTagName("article");
        var todasReveladas = true;

        for (var i = 0; i < cartas.length; i++) {
            if (cartas[i].dataset.estado !== "revelada") {
                todasReveladas = false;
            }
        }

        if (todasReveladas) {
            alert("¡Has terminado el juego!");
            this.cronometro.parar();
        }
    }

    deshabilitarCartas() {
        this.#primera_carta.dataset.estado = "revelada";
        this.#segunda_carta.dataset.estado = "revelada";

        this.#comprobarJuego();
        this.#reiniciarAtributos();
    }

    #cubrirCartas() {
        var objeto = this;
        this.#tablero_bloqueado = true;

        setTimeout(function() {
            objeto.#primera_carta.removeAttribute("data-estado");
            objeto.#segunda_carta.removeAttribute("data-estado");
            objeto.#reiniciarAtributos();
        }, 500);
    }

    #comprobarPareja() {
        var img1 = this.#primera_carta.children[1].getAttribute("src");
        var img2 = this.#segunda_carta.children[1].getAttribute("src");

        if (img1 === img2) {
            this.deshabilitarCartas();
        } else {
            this.#cubrirCartas();
        }
    }

    voltearCarta(carta) {
        if (this.#tablero_bloqueado) {
            return;
        }

        if (carta.dataset.estado === "volteada" || carta.dataset.estado === "revelada") {
            return;
        }

        carta.dataset.estado = "volteada";

        if (this.#primera_carta === null) {
            this.#primera_carta = carta;
            return;
        }

        this.#segunda_carta = carta;
        this.#tablero_bloqueado = true;

        var objeto = this;
        setTimeout(function() {
            objeto.#comprobarPareja();
        }, 100);
    }
}

var juegoMemoria = new Memoria();
