<?php
session_start();
class Cronometro{    
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

$mensaje = "";

$crono = new Cronometro();

if (isset($_SESSION['crono_inicio']) && isset($_SESSION['crono_transcurrido'])) {
    $crono->setEstado($_SESSION['crono_inicio'], $_SESSION['crono_transcurrido']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['arrancar'])) {
        $crono->arrancar();
        $mensaje = "Cronómetro arrancado.";
    }

    if (isset($_POST['parar'])) {
        $crono->parar();
        $mensaje = "Cronómetro parado.";
    }

    if (isset($_POST['mostrar'])) {
        $mensaje = $crono->mostrar();
    }

    $_SESSION['crono_inicio'] = $crono->getInicio();
    $_SESSION['crono_transcurrido'] = $crono->getTranscurrido();
}

?>
<!DOCTYPE HTML>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <title>Cronómetro PHP - MotoGP</title> <meta name ="author" content ="Alejandro Requena" />
        <meta name ="description" content ="Pruebas de la clase Cronómetro" /> 
        <meta name ="keywords" content ="" />
        <meta name ="viewport" content ="width=device-width, initial-scale=1.0" />

        <link rel="stylesheet" type="text/css" href="../estilo/estilo.css"/>
        <link rel="stylesheet" type="text/css" href="../estilo/layout.css"/>
        <link rel="icon" href="../multimedia/iconoMotoGP.ico"/>

        </head>

    <body>
        <header>
            <h1><a href="../index.html">MotoGP Desktop</a></h1>
            <nav>
                <a href="../index.html" title="Inicio de la aplicación MotoGP Desktop"> Inicio </a>
                <a href="../piloto.html" title="Información sobre el piloto"> Piloto </a>
                <a href="../meteorología.html" title="Información sobre la meteorología"> Meteorología </a>
                <a href="../juegos.html" title="Juegos sobre MotoGP"> Juegos </a>
                <a href="clasificaciones.php" title="Clasificaciones de MotoGP"> Clasificaciones </a>
                <a href="../circuito.html" title="Información y estadísticas de el circuito asignado"> Circuito </a>
                <a href="../ayuda.html" title="Ayuda sobre la aplicación de MotoGP"> Ayuda </a>
            </nav>
        </header>
        <section>
            <p>Estás en: <a href="../index.html">Inicio</a> >> <a href="../juegos.html">Juegos</a> >> <strong>Cronómetro en PHP</strong></p>
        </section>

        <main>
            <h2>Cronómetro</h2>
            

            <form method="POST" action="cronometro.php">
                <button name="arrancar" value="true">Arrancar</button>
                <button name="parar" value="true">Parar</button>
                <button name="mostrar" value="true">Mostrar Tiempo</button>
            </form>

            <?php
            if (!empty($mensaje)) {
                echo "<h3>Resultado:</h3>";
                echo "<p>" . htmlspecialchars($mensaje) . "</p>";
            }
            ?>
        </main>
        
        </body>
</html>