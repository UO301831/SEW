class Ciudad{
    constructor(ciudad,pais,gentilicio){
        this.ciduad = ciudad
        this.gentilicio = gentilicio
        this.pais = pais
    }

    initialize_Geo(cantidadPoblacion,puntoCentral) {
        this.puntoCentral = puntoCentral
        this.cantidadPoblacion = cantidadPoblacion
    }

    cityToString(){
        // TODO agregar el doc.write
        return this.ciudad
    }

    countryToString(){
        //TODO agregar el doc.write
        return this.pais;
    }




}