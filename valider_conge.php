<?php
$conn = new mysqli("localhost","root","","personnel");

$id = $_GET['id'];
$role = $_GET['role'];
$action = $_GET['action'];

if($role == "bureau"){
    $statut = ($action == "valider") ? "VALIDE_BUREAU" : "REFUSE_BUREAU";
}

elseif($role == "division"){
    $statut = ($action == "valider") ? "VALIDE_DIVISION" : "REFUSE_DIVISION";
}

elseif($role == "directeur"){
    $statut = ($action == "valider") ? "VALIDE_DIRECTEUR" : "REFUSE_DIRECTEUR";
}

elseif($role == "rh"){
    $statut = ($action == "valider") ? "VALIDE_RH" : "REFUSE_RH";
}

$sql = "UPDATE demandes_conges SET statut=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $statut, $id);
$stmt->execute();

header("Location: dashboard.php");
?>