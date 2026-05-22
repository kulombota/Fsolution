<?php
$conn = new mysqli("localhost", "root", "", "personnel");

if($conn->connect_error){
    die("Erreur connexion : " . $conn->connect_error);
}
?>