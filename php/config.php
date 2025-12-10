<?php
$host = "localhost";
$user = "DBUSER2025";
$pass = "DBPSWD2025";
$db = "UO301831_DB";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    exit("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>