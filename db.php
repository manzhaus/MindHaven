<?php
$servername = "localhost";
$username = "root"; // default for XAMPP/Laragon
$password = ""; // default no password
$dbname = "mindhaven";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
