<?php
class Clasificacion {
    protected $documento;
    protected $xml;

    public function __construct() {
        $this->documento = "./xml/circuitoEsquema.xml";
    }

    public function consultar() {
        $datos = file_get_contents($this->documento);
        if ($datos === false) {
            echo "<p>Error: No se pudo leer el archivo XML.</p>";
        } else {
            try {
                $this->xml = new SimpleXMLElement($datos);
                $this->xml->registerXPathNamespace('datos', 'http://www.uniovi.es');
            } catch (Exception $e) {
                echo "<p>Error interpretando el XML: " . $e->getMessage() . "</p>";
            }
        }
    }

    public function obtenerGanador() {
        if ($this->xml) {
            $nodosVencedor = $this->xml->xpath('//datos:vencedor');
            
            if (!empty($nodosVencedor)) {
                $vencedor = $nodosVencedor[0];
                $nombre = $vencedor['nombre'];
                $tiempo = $vencedor['tiempo'];
                // --- FORMATEO DEL TIEMPO ---
                $tiempoFormateado = str_replace('PT', '', $tiempo);
                $tiempoFormateado = str_replace('M', ' min ', $tiempoFormateado);
                $tiempoFormateado = str_replace('S', ' seg', $tiempoFormateado);
               
                echo "<section id='ganador'>";
                echo "<h3>Ganador de la carrera</h3>";
                echo "<p><strong>Piloto:</strong> " . $nombre . "</p>";
                echo "<p><strong>Tiempo:</strong> " . $tiempoFormateado . "</p>";
                echo "</section>";
            }
        }
    }

    public function obtenerClasificacion() {
        if ($this->xml) {
            $pilotos = $this->xml->xpath('//datos:clasificacion/datos:piloto');

            echo "<section id='clasificacionMundial'>";
            echo "<h3>Clasificación del Mundial</h3>";
            echo "<table>";
            echo "<caption>Tabla de puntos tras la carrera</caption>";
            echo "<thead>";
            echo "<tr>";
            echo "<th scope='col'>Puesto</th>";
            echo "<th scope='col'>Piloto</th>";
            echo "<th scope='col'>Puntos</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            foreach ($pilotos as $piloto) {
                echo "<tr>";
                echo "<td>" . $piloto['puesto'] . "</td>";
                echo "<td>" . $piloto['nombre'] . "</td>";
                echo "<td>" . $piloto['puntos'] . "</td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
            echo "</section>";
        }
    }
}

$clasificacion = new Clasificacion();
$clasificacion->consultar();
?>






<!DOCTYPE HTML>

<html lang="es">
<head>
    <!-- Datos que describen el documento -->
    <meta charset="UTF-8" />
    <title>MotoGP - Clasificaciones</title>

    <meta name="author" content="Alejandro Requena" />
    <meta name="description" content="Información sobre la clasificación de MotoGP" />
    <meta name="keywords" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />


    <link rel="stylesheet" type="text/css" href="estilo/estilo.css"/>
    <link rel="stylesheet" type="text/css" href="estilo/layout.css"/>
    <link rel="icon" href="multimedia/iconoMotoGP.ico"/>

</head>

<body>
    <header>
        <h1><a href="index.html">MotoGP Desktop</a></h1>
        <nav>
            <a href="index.html" title="Inicio de la aplicación MotoGP Desktop"> Inicio </a>
            <a href="piloto.html" title="Información sobre el piloto"> Piloto </a>
            <a href="meteorología.html" title="Información sobre la meteorología"> Meteorología </a>
            <a href="juegos.html" title="Juegos sobre MotoGP"> Juegos </a>
            <a class = "active" href="clasificaciones.php" title="Clasificaciones de MotoGP"> Clasificaciones </a>
            <a href="circuito.html" title="Información y estadísticas de el circuito asignado"> Circuito </a>
            <a href="ayuda.html" title="Ayuda sobre la aplicación de MotoGP"> Ayuda </a>
        </nav>
    </header>
    
<section id = "migas"> <p>Estás en: <a href="index.html">Inicio</a> >> <strong> Clasificaciones </strong></p> </section>
    <h2>Clasificaciones MotoGP</h2>
    <?php
        $clasificacion->obtenerGanador();

        $clasificacion->obtenerClasificacion();
    ?>
</body>
</html>