class Memoria{
    constructor(){
    }

    voltearCarta(carta) {
        if (!carta.dataset.estado || carta.dataset.estado != "volteada") {
            carta.dataset.estado = "volteada";
        } else {
            carta.removeAttribute("data-estado");
        }
    }
    
}
const juegoMemoria = new Memoria();
