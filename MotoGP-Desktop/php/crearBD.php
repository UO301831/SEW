<?php
$host = "localhost";
$user = "DBUSER2025";
$pass = "DBPSWD2025";

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Error conectando a MySQL: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS UO301831_DB";

if ($conn->query($sql) === TRUE) {
    echo "Base de datos creada correctamente.";
} else {
    echo "Error al crear la base de datos: " . $conn->error;
}

$conn->close();
?>
