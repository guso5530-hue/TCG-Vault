<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "tcg_vault";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão com o TCG Vault: " . $conn->connect_error);
}
?>