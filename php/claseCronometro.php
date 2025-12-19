<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}class Cronometro{    
    private $inicio;
    private $transcurrido;

    public function __construct(){
        $this->inicio = 0;
        $this->transcurrido = 0;
    }

    public function arrancar(){
        $this->inicio = microtime(true);
    }

    public function parar(){ 
        if($this->inicio > 0){
            $this->transcurrido = microtime(true) - $this->inicio;
            $this->inicio = 0;
            return $this->transcurrido;
        }
    }

    public function mostrar(){
        if ($this->transcurrido == 0 && $this->inicio == 0) {
            return "00:00.0";
        }

        $minutos = floor($this->transcurrido / 60);
        
        $segundos = fmod($this->transcurrido, 60);

        return sprintf("%02d:%04.1f", $minutos, $segundos);
    }

    public function getInicio() {
        return $this->inicio;
    }

    public function getTranscurrido() {
        return $this->transcurrido;
    }
    public function setEstado($inicio, $transcurrido) {
        $this->inicio = $inicio;
        $this->transcurrido = $transcurrido;
    }
}
