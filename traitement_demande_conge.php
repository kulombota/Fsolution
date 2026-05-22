<?php
session_start();

$conn = new mysqli("localhost","root","","personnel");

if($conn->connect_error){
    die("Erreur connexion DB");
}

/* Vérifier session */
if(!isset($_SESSION['user_id'])){
    header("Location: connexion.php");
    exit;
}

/* Récupération des données formulaire */
$employe_id = $_POST['employe_id'];
$date_debut = $_POST['date_debut'];
$date_fin = $_POST['date_fin'];
$motif = $_POST['motif'];

/* =========================
   GÉNÉRATION NUMÉRO DEMANDE
   ========================= */
$year = date("Y");

$count = $conn->query("SELECT COUNT(*) as total FROM demandes_conges WHERE annee = $year");
$row = $count->fetch_assoc();

$numero = "CG-" . $year . "-" . str_pad($row['total'] + 1, 4, "0", STR_PAD_LEFT);

/* =========================
   INSERTION COMPLETE
   ========================= */

$sql = "INSERT INTO demandes_conges
(
    employe_id,
    date_debut,
    date_fin,
    motif,
    statut,
    annee,
    numero_demande
)
VALUES (?, ?, ?, ?, 'EN_ATTENTE_BUREAU', YEAR(CURDATE()), ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "issss",
    $employe_id,
    $date_debut,
    $date_fin,
    $motif,
    $numero
);

if($stmt->execute()){
    header("Location: demande_conge.php?success=1");
}else{
    echo "Erreur enregistrement : " . $conn->error;
}
?>