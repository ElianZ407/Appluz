<?php

function conectarDB() {
    $host = 'localhost';
    $usuario = 'root';     
    $password = '';        
    $dbname = 'db_luz'; 

    $conexion = new mysqli($host, $usuario, $password, $dbname);

    if ($conexion->connect_error) {
       
        die("Error de conexión: " . $conexion->connect_error);
    }

       $conexion->query("SET time_zone = '-06:00'"); // Para horario estándar de México

    $conexion->set_charset("utf8mb4");
    return $conexion;
}
?>