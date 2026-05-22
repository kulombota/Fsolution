<?php
session_start();
$conn = new mysqli("localhost","root","","personnel");

if($conn->connect_error){
    die("Erreur connexion DB");
}

$agent = null;
$erreur = "";

/* RECHERCHE PAR MATRICULE */
if(isset($_GET['matricule'])){

    $matricule = $_GET['matricule'];

    $sql = "SELECT * FROM employes WHERE matricule = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $matricule);
    $stmt->execute();
    $result = $stmt->get_result();

    $agent = $result->fetch_assoc();

    if(!$agent){
        $erreur = "❌ Matricule introuvable";
    }
}

/* TRAITEMENT DEMANDE */
if(isset($_POST['envoyer'])){

    $employe_id = $_POST['employe_id'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $motif = $_POST['motif'];

    $sql = "INSERT INTO demandes_conges
            (employe_id, date_debut, date_fin, motif)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $employe_id, $date_debut, $date_fin, $motif);
    $stmt->execute();

    header("Location: demande_conge.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Demande de congé</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f4f6f9;
    font-family:Arial;
}

.container{
    max-width:650px;
    margin-top:40px;
}

.card{
    border:none;
    box-shadow:0 3px 10px rgba(0,0,0,0.1);
    border-radius:10px;
}

.card-header{
    background:#0d6efd;
    color:white;
    text-align:center;
    font-weight:bold;
}

.info{
    background:#f8f9fa;
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
}

</style>
</head>

<body>

<div class="container">

<div class="card">

<div class="card-header">
🏖️ Demande de Congé Automatique
</div>

<div class="card-body">

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success">
✅ Demande envoyée avec succès
</div>
<?php endif; ?>

<!-- FORMULAIRE MATRICULE -->
<form method="GET">

    <label>Entrer votre matricule</label>
    <input type="text" name="matricule" class="form-control" required>

    <button class="btn btn-primary mt-2 w-100">
        🔍 Rechercher
    </button>

</form>

<br>

<?php if($erreur): ?>
<div class="alert alert-danger"><?= $erreur ?></div>
<?php endif; ?>

<!-- AFFICHAGE AGENT -->
<?php if($agent): ?>

<div class="info">
    <strong>Informations Agent</strong><br><br>

    Nom : <?= $agent['nom'] ?><br>
    Postnom : <?= $agent['postnom'] ?><br>
    Prénom : <?= $agent['prenom'] ?><br>
    Matricule : <?= $agent['matricule'] ?><br>
    Direction : <?= $agent['direction'] ?><br>
    Fonction : <?= $agent['fonction'] ?><br>
</div>

<!-- FORMULAIRE DEMANDE -->
<form method="POST">

    <input type="hidden" name="employe_id" value="<?= $agent['id'] ?>">

    <div class="mb-3">
        <label>Date début</label>
        <input type="date" name="date_debut" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Date fin</label>
        <input type="date" name="date_fin" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Motif</label>
        <textarea name="motif" class="form-control" required></textarea>
    </div>

    <button name="envoyer" class="btn btn-success w-100">
        📤 Envoyer la demande
    </button>
    

</form>

<?php endif; ?>

</div>
</div>
</div>

</body>
</html>