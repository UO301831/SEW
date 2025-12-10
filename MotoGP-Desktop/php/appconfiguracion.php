<?php
require_once "config.php";
require_once "Configuracion.php";

$cfg = new Configuracion($conn);
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["reiniciar"])) {
        $mensaje = $cfg->reiniciarBD();
    }

    if (isset($_POST["eliminar"])) {
        $mensaje = $cfg->eliminarBD();
    }

    if (isset($_POST["exportar"])) {
        $mensaje = $cfg->exportarCSV();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración – Usabilidad MotoGP</title>
    <link rel="stylesheet" href="../estilo/estilo.css">
    <link rel="stylesheet" href="../estilo/layout.css">

</head>
<body>

    <header>
        <h1>Configuración de Base de Datos – Usabilidad</h1>
    </header>

    <main>
        <h2>Opciones de gestión</h2>

        <form method="POST">
            <button type="submit" name="reiniciar">Reiniciar Base de Datos</button>
            <button type="submit" name="eliminar">Eliminar Base de Datos</button>
            <button type="submit" name="exportar">Exportar datos (.csv)</button>
        </form>

        <?php if (!empty($mensaje)): ?>
            <p class="mensaje"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>
    </main>

</body>
</html>
