class Circuito{
    comprobarApiFile(){
        if (window.File && window.FileReader && window.FileList && window.Blob){
            this.leerArchivoHTML();
        }else{
            alert("El navegador no soporta el API File.")
        }
    }

    leerArchivoHTML(){
        var lector = new FileReader();
        lector.onload = function(){

        }
    }
}