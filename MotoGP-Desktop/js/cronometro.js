class Cronometro {

    constructor() {
        this.tiempo = 0;      
        this.inicio = null;    
        this.corriendo = null; 
    }

    arrancar() {
            try {
                this.inicio = Temporal.Now.instant();
            } catch (e) {
                this.inicio = new Date();
            }
            this.corriendo = setInterval(this.actualizar.bind(this), 100);
    }

    actualizar() {
        let ahora;

        try {
            ahora = Temporal.Now.instant();
            this.tiempo = ahora.epochMilliseconds - this.inicio.epochMilliseconds;
        } catch (e) {
            ahora = new Date();
            this.tiempo = ahora.getTime() - this.inicio.getTime();
        }

        this.mostrar();
    }

    mostrar() {
        let minutos = parseInt(this.tiempo / 60000);
        let segundos = parseInt((this.tiempo % 60000) / 1000);
        let decimas = parseInt((this.tiempo % 1000) / 100);

        let mm = String(minutos).padStart(2, '0');
        let ss = String(segundos).padStart(2, '0');

        const texto = `${mm}:${ss}.${decimas}`;

        const p = document.querySelector("main p");
        if (p) p.textContent = texto;
    }

    parar() {
        clearInterval(this.corriendo);
        this.corriendo = null;
    }

    reiniciar() {
        clearInterval(this.corriendo);
        this.corriendo = null;
        this.tiempo = 0;
        this.mostrar();
    }
}
