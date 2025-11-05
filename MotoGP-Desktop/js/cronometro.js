class Cronometro{

    constructor(){
        this.tiempo = 0;
    }

    arrancar(){
        try{
            this.inicio = Temporal.Now.instant();
        }catch(Exception){
            this.inicio = new Date();
        } 
       
    }

    actualizar(){
        
    }

}