<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: connexion.php");
    exit;
}

$conn = new mysqli("localhost","root","","personnel");
if($conn->connect_error) die("Erreur DB : ".$conn->connect_error);

$action = $_GET['action'] ?? '';
$ids = $_GET['ids'] ?? '';
$id = $_GET['id'] ?? '';

if($id && !$ids) $ids = $id;
if(!$ids) die("Aucun agent sélectionné");

// transformer en tableau
$ids_array = explode(",", $ids);

// Vérifier si la table conges existe sinon la créer
$conn->query("
CREATE TABLE IF NOT EXISTS conges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employe_id INT NOT NULL,
    date_conge DATE NOT NULL,
    deja_conge TINYINT(1) DEFAULT 0,
    FOREIGN KEY (employe_id) REFERENCES employes(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

foreach($ids_array as $emp_id){
    $emp_id = intval($emp_id);
    if($action == 'give'){
        // Vérifier si un congé existe déjà ce mois
        $stmt = $conn->prepare("SELECT id FROM conges WHERE employe_id=? AND MONTH(date_conge)=MONTH(CURDATE())");
        $stmt->bind_param("i",$emp_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->num_rows == 0){
            $stmt_insert = $conn->prepare("INSERT INTO conges (employe_id, date_conge, deja_conge) VALUES (?, CURDATE(), 1)");
            $stmt_insert->bind_param("i",$emp_id);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt->close();
    } elseif($action == 'cancel'){
        $stmt_del = $conn->prepare("DELETE FROM conges WHERE employe_id=? AND MONTH(date_conge)=MONTH(CURDATE())");
        $stmt_del->bind_param("i",$emp_id);
        $stmt_del->execute();
        $stmt_del->close();
    }
}

$conn->close();

// redirection vers la page principale
header("Location: gestionconge.php");
exit;