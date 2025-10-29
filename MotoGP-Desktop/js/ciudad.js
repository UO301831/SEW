class Ciudad{
    constructor(ciudad,pais,gentilicio){
        this.ciudad = ciudad
        this.gentilicio = gentilicio
        this.pais = pais
    }

    initialize_Geo(cantidadPoblacion,puntoCentral) {
        this.puntoCentral = puntoCentral
        this.cantidadPoblacion = cantidadPoblacion
    }

    cityToString(){
       
        return this.ciudad
    }

    countryToString(){
       
        return this.pais;
    }

    buildDemographicInfo(){
        const lista = document.createElement("ul")
        const liPunto = document.createElement("li")
        const liCoords = document.createElement("li")
        liPunto.textContent = this.puntoCentral
        liCoords.textContent = this.cantidadPoblacion
    }


}