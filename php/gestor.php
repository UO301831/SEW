<?php
require_once "Configuracion.php";

// Instanciamos la clase maestra
$gestor = new Configuracion();
$mensaje = "";

// Procesar acciones de los botones
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    if (isset($_POST["crear"])) {
        $mensaje = $gestor->crearBaseDatos();
    }
    
    if (isset($_POST["reiniciar"])) {
        $mensaje = $gestor->reiniciarDatos();
    }

    if (isset($_POST["eliminar"])) {
        $mensaje = $gestor->eliminarBaseDatos();
    }

    if (isset($_POST["exportar"])) {
        $mensaje = $gestor->exportarCSV();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración – Usabilidad MotoGP</title>
    <link rel="stylesheet" href="../estilo/estilo.css">
    <link rel="stylesheet" href="../estilo/layout.css">
</head>
<body>

    <header>
        <h1>Gestor de Base de Datos - Pruebas de Usabilidad</h1>
    </header>

    <main>
        <h2>Panel de Administración</h2>
        
        <?php if (!empty($mensaje)): ?>
            <div class="msg"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form method="POST">
            <button type="submit" name="crear">Crear Base de Datos y Tablas</button>
            
            <button type="submit" name="reiniciar">Reiniciar Base de Datos (Borrar Datos)</button>
            
            <button type="submit" name="exportar">Exportar Datos a CSV</button>
            
            <button type="submit" name="eliminar">Eliminar Base de Datos Completamente</button>
        </form>
        
        <a href="../index.html"> Volver a index</a>
    </main>

</body>
</html>