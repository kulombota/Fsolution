
<?php
$conn = new mysqli("localhost","root","","personnel");

$id = $_GET['id'];

$conn->query("DELETE FROM autorisation_soins WHERE id=$id");

header("Location: liste_autorisations.php");