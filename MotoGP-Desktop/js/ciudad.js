class Ciudad {
    constructor(ciudad, pais, gentilicio) {
        this.ciudad = ciudad;
        this.gentilicio = gentilicio;
        this.pais = pais;
        this.puntoCentral = null;
        this.cantidadPoblacion = null;
    }

    initializeGeo(cantidadPoblacion, puntoCentral) {
        this.cantidadPoblacion = cantidadPoblacion;
        this.puntoCentral = puntoCentral;
    }

    cityToString() {
        return this.ciudad;
    }

    countryToString() {
        return this.pais;
    }

    buildDemographicInfo() {
        return `
            <ul>
                <li>Gentilicio: ${this.gentilicio}</li>
                <li>Población: ${this.cantidadPoblacion}</li>
            </ul>
        `;
    }

    writeCoordinates() {
        const p = document.createElement("p");
        p.innerHTML = `Coordenadas del punto central: ${this.puntoCentral}`;
        document.getElementById("infoCiudad").appendChild(p);
    }
}
