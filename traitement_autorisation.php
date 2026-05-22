<?php
$conn = new mysqli("localhost","root","","personnel");
if($conn->connect_error) die("Erreur DB");

$nom = $_POST['nom'];
$postnom = $_POST['postnom'];
$prenom = $_POST['prenom'];
$matricule = $_POST['matricule'];
$direction_id = $_POST['direction_id'];
$fonction = $_POST['fonction'];
$telephone = $_POST['telephone'];
$hopital = $_POST['hopital'];
$date_consultation = $_POST['date_consultation'];
$motif = $_POST['motif'];
$date_demande = date("Y-m-d");

$sql = "INSERT INTO autorisation_soins 
(nom, postnom, prenom, matricule, direction_id, fonction, telephone, hopital, motif, date_consultation, date_demande)
VALUES 
('$nom','$postnom','$prenom','$matricule','$direction_id','$fonction','$telephone','$hopital','$motif','$date_consultation','$date_demande')";

if($conn->query($sql)){
    $id = $conn->insert_id;
    header("Location: imprime_autorisation.php?id=".$id);
} else {
    echo "Erreur : ".$conn->error;
}