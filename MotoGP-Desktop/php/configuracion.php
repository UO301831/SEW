<?php
class Configuracion {

    private $conn;
    private $dbName = "UO301831_DB"; 

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* ----------------------------------------------------
       REINICIAR BASE DE DATOS
       ---------------------------------------------------- */
    public function reiniciarBD() {
        // Orden importante para respetar Foreign Keys al borrar
        $tablas = ["respuestas", "consideration", "test_info", "user_info", 
                   "profesion", "genero", "dispositivo"];

        // Desactivar check de llaves foráneas temporalmente para evitar errores al vaciar
        $this->conn->query("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($tablas as $t) {
            $this->conn->query("DELETE FROM $t");
            // Reiniciar el auto_increment para que los IDs empiecen de 1 de nuevo
            $this->conn->query("ALTER TABLE $t AUTO_INCREMENT = 1");
        }

        $this->conn->query("SET FOREIGN_KEY_CHECKS = 1");

        return "Base de datos reiniciada correctamente.";
    }

    /* ----------------------------------------------------
       ELIMINAR BASE DE DATOS COMPLETA
       ---------------------------------------------------- */
    public function eliminarBD() {
        $sql = "DROP DATABASE IF EXISTS {$this->dbName}";
        
        if ($this->conn->query($sql) === TRUE) {
            return "Base de datos eliminada correctamente.";
        }
        return "Error al eliminar la base de datos: " . $this->conn->error;
    }

    /* ----------------------------------------------------
       EXPORTAR A CSV (Optimizado para Excel)
       ---------------------------------------------------- */
    public function exportarCSV() {
        $filename = "export_usabilidad_" . date("Ymd_His") . ".csv";
        // Asegurarse de que la ruta existe, si no, usar carpeta temporal o actual
        if (!file_exists("../export")) {
            mkdir("../export", 0777, true);
        }
        $filepath = "../export/" . $filename;

        $file = fopen($filepath, "w");

        // 1. AÑADIR BOM PARA UTF-8 (Para que Excel lea bien tildes y ñ)
        fputs($file, "\xEF\xBB\xBF");

        // 2. DEFINIR SEPARADOR (Punto y coma es mejor para Excel en español)
        $sep = ";";

        // Encabezados
        fputcsv($file, [
            "Usuario",
            "Profesión",
            "Edad",
            "Género",
            "Pericia",
            "Dispositivo",
            "Tiempo (s)",
            "Completado (1=Si, 0=No)",
            "Comentarios",
            "Propuestas",
            "Valoración"
        ], $sep);

        // Tu consulta SQL original (Es correcta para los datos que necesitas)
        $sql = "
            SELECT 
                u.id, 
                COALESCE(p.nombre, 'Sin definir') AS profesion, 
                u.edad, 
                COALESCE(g.nombre, 'Sin definir') AS genero, 
                u.pericia,
                COALESCE(d.nombre, 'Sin definir') AS dispositivo, 
                t.tiempo_tardado, 
                COALESCE(t.completado, 0) as completado, 
                t.comentarios, 
                t.propuestas, 
                t.valoracion
            FROM user_info u
            LEFT JOIN profesion p ON u.id_profesion = p.id_profesion
            LEFT JOIN genero g ON u.id_genero = g.id_genero
            LEFT JOIN test_info t ON u.id = t.idUsuario
            LEFT JOIN dispositivo d ON t.id_dispositivo = d.id_dispositivo
        ";

        $resultado = $this->conn->query($sql);

        while ($fila = $resultado->fetch_assoc()) {
            // Limpiar saltos de línea en comentarios para no romper el CSV
            if(isset($fila['comentarios'])) $fila['comentarios'] = str_replace(["\r", "\n"], " ", $fila['comentarios']);
            if(isset($fila['propuestas'])) $fila['propuestas'] = str_replace(["\r", "\n"], " ", $fila['propuestas']);

            fputcsv($file, $fila, $sep);
        }

        fclose($file);

        return "Datos exportados correctamente en: $filename";
    }
}
?>