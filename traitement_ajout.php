<<?php
session_start();

// 🔐 Sécurité : vérifier connexion
if(!isset($_SESSION['user_id'])){
    header("Location: connexion.php");
    exit;
}

$user_id = $_SESSION['user_id']; // ✅ sécurisé

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion DB
$conn = new mysqli("localhost", "root", "", "personnel");
if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

// Vérifier soumission
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    // Fonction upload sécurisée
    function uploadFile($file){
        if(isset($file) && $file['error'] === 0){

            $dossier = "uploads/";

            if(!is_dir($dossier)){
                mkdir($dossier, 0777, true);
            }

            $nomFichier = time() . "_" . basename($file['name']);
            $chemin = $dossier . $nomFichier;

            if(move_uploaded_file($file['tmp_name'], $chemin)){
                return $chemin;
            }
        }
        return null;
    }

    // Upload fichiers
    $piece_identite = uploadFile($_FILES['piece_identite'] ?? null);
    $acte_engagement = uploadFile($_FILES['acte_engagement'] ?? null);
    $diplome = uploadFile($_FILES['diplome'] ?? null);
    $cv = uploadFile($_FILES['cv'] ?? null);
    $photo = uploadFile($_FILES['photo'] ?? null);

    // Données
    $nom = $_POST['nom'] ?? '';
    $postnom = $_POST['postnom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $sexe = $_POST['sexe'] ?? '';
    $naissance = $_POST['naissance'] ?? '';
    $matricule = $_POST['matricule'] ?? '';
    $telephone = $_POST['telephone'] ?? '';

    $direction = $_POST['direction'] ?? '';
    $division = $_POST['division'] ?? '';
    $bureau = $_POST['bureau'] ?? '';
    $fonction = $_POST['fonction'] ?? '';

    $type_piece = $_POST['type_piece'] ?? '';

    $recrutement = $_POST['recrutement'] ?? '';
    $contrat = $_POST['contrat'] ?? '';
    $grade = $_POST['grade'] ?? '';
    $niveau = $_POST['NiveauEtude'] ?? '';
    $domaine = $_POST['Dommaine'] ?? '';
    $situation = $_POST['situation'] ?? '';

    // Préparation SQL
    $stmt = $conn->prepare("
    INSERT INTO employes (
        nom, postnom, prenom, sexe, date_naissance, matricule, telephone,
        direction, division, bureau, fonction,
        type_piece, piece_identite, acte_engagement, diplome, cv, photo,
        date_embauche, contrat, grade, niveau_etude, domaine, situation_matrimoniale,
        user_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if(!$stmt){
        die("Erreur préparation : " . $conn->error);
    }

    $stmt->bind_param("sssssssssssssssssssssssi",
        $nom, $postnom, $prenom, $sexe, $naissance, $matricule, $telephone,
        $direction, $division, $bureau, $fonction,
        $type_piece, $piece_identite, $acte_engagement, $diplome, $cv, $photo,
        $recrutement, $contrat, $grade, $niveau, $domaine, $situation,
        $user_id
    );

    if($stmt->execute()){
        header("Location: listeagent.php"); // plus besoin de user_id
        exit;
    } else {
        echo "❌ Erreur SQL : " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>