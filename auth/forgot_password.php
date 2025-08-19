<?php
$servername = "localhost"; // o el host de tu hosting
$username   = "root";      // cambia según tu hosting (en InfinityFree NO es root)
$password   = "";          // cambia según tu hosting
$database   = "tu_base";   // el nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Ejemplo de consulta
$sql = "SELECT id, nombre, correo FROM usuarios";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Mostrar registros
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"] . " - Nombre: " . $row["nombre"] . " - Correo: " . $row["correo"] . "<br>";
    }
} else {
    echo "0 resultados";
}

$conn->close();
?>
