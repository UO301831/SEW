<?php
// cronometro.php (La vista del juego)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// IMPORTANTE: Aquí incluimos la clase separada
require_once 'claseCronometro.php';

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
        <title>Cronómetro PHP - MotoGP</title>
        <link rel="stylesheet" type="text/css" href="../estilo/estilo.css"/>
        <link rel="stylesheet" type="text/css" href="../estilo/layout.css"/>
    </head>
    <body>
        <header>
            <h1><a href="../index.html">MotoGP Desktop</a></h1>
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