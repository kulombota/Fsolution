<?php
$conn = new mysqli("localhost","root","","personnel");
if($conn->connect_error) die("Erreur DB : ".$conn->connect_error);

$id = $_GET['id'] ?? 0;
$id = intval($id); // Sécurité contre injection

$sql = "SELECT * FROM employes WHERE id=$id";
$result = $conn->query($sql);

if(!$result || $result->num_rows == 0){
    die("Employé introuvable !");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Détails Employé</title>

<!-- Lien Font Awesome pour les icônes -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f1f5f9;
    margin: 0;
}

.container {
    max-width: 900px;
    margin: auto;
    background: white;
    padding: 20px 30px;
    border-radius: 12px;
    margin-top: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

h2 {
    text-align: center;
    color: #1e293b;
}

.section {
    margin-bottom: 25px;
}

.section h3 {
    border-bottom: 2px solid #2563eb;
    padding-bottom: 5px;
    color: #2563eb;
}

.info-row {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}

.info-row b {
    width: 160px;
    color: #1e293b;
}

.photo {
    display: block;
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 12px;
    margin: 0 auto 20px auto;
    border: 2px solid #2563eb;
}

.btn {
    display: inline-block;
    padding: 8px 12px;
    background: #2563eb;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    margin: 5px 2px;
    font-size: 14px;
}

.btn-doc {
    background: #16a34a;
}

.btn-print {
    background: #f59e0b;
}

.btn-pdf {
    background: #dc2626;
}

button {
    cursor: pointer;
}

/* Masquer les boutons lors de l'impression */
@media print {
    .no-print {
        display: none;
    }
}
</style>
</head>

<body>

<div class="container">

<h2>👤 <?= $row['nom']." ".$row['postnom']." ".$row['prenom'] ?></h2>

<img class="photo" src="<?= !empty($row['photo']) ? $row['photo'] : 'uploads/default.png' ?>">

<!-- BOUTONS IMPRESSION / PDF -->
<div class="no-print" style="text-align:center; margin-bottom:20px;">
    <button class="btn btn-print" onclick="window.print();"><i class="fas fa-print"></i> Imprimer</button>
    <button class="btn btn-pdf" onclick="exportPDF();"><i class="fas fa-file-pdf"></i> Exporter PDF</button>
    <a class="btn" href="listeagent.php"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<!-- IDENTIFICATION -->
<div class="section">
<h3>1️⃣ Identification</h3>
<div class="info-row"><b>Nom :</b> <?= $row['nom'] ?></div>
<div class="info-row"><b>Post-Nom :</b> <?= $row['postnom'] ?></div>
<div class="info-row"><b>Prénom :</b> <?= $row['prenom'] ?></div>
<div class="info-row"><b>Sexe :</b> <?= $row['sexe'] ?></div>
<div class="info-row"><b>Date de naissance :</b> <?= $row['date_naissance'] ?></div>
<div class="info-row"><b>Matricule :</b> <?= $row['matricule'] ?></div>
<div class="info-row"><b>Téléphone :</b> <?= $row['telephone'] ?></div>
</div>

<!-- AFFECTATION -->
<div class="section">
<h3>2️⃣ Affectation</h3>
<div class="info-row"><b>Direction :</b> <?= $row['direction'] ?></div>
<div class="info-row"><b>Division :</b> <?= $row['division'] ?></div>
<div class="info-row"><b>Bureau :</b> <?= $row['bureau'] ?></div>
<div class="info-row"><b>Fonction :</b> <?= $row['fonction'] ?></div>
</div>

<!-- DOSSIER -->
<div class="section">
<h3>3️⃣ Dossier</h3>
<div class="info-row"><b>Type de pièce :</b> <?= $row['type_piece'] ?></div>

<div class="info-row">
<b>Pièce identité :</b>
<?php if($row['piece_identite']): ?>
<a class="btn btn-doc" href="<?= $row['piece_identite'] ?>" target="_blank"><i class="fas fa-file"></i> Voir</a>
<?php else: ?>Aucun<?php endif; ?>
</div>

<div class="info-row">
<b>Acte d’engagement :</b>
<?php if($row['acte_engagement']): ?>
<a class="btn btn-doc" href="<?= $row['acte_engagement'] ?>" target="_blank"><i class="fas fa-file"></i> Voir</a>
<?php else: ?>Aucun<?php endif; ?>
</div>

<div class="info-row">
<b>Diplôme :</b>
<?php if($row['diplome']): ?>
<a class="btn btn-doc" href="<?= $row['diplome'] ?>" target="_blank"><i class="fas fa-file"></i> Voir</a>
<?php else: ?>Aucun<?php endif; ?>
</div>

<div class="info-row">
<b>CV :</b>
<?php if($row['cv']): ?>
<a class="btn btn-doc" href="<?= $row['cv'] ?>" target="_blank"><i class="fas fa-file"></i> Voir</a>
<?php else: ?>Aucun<?php endif; ?>
</div>
</div>

<!-- INFORMATIONS RH -->
<div class="section">
<h3>4️⃣ Informations RH</h3>
<div class="info-row"><b>Date d'embauche :</b> <?= $row['date_embauche'] ?></div>
<div class="info-row"><b>Contrat :</b> <?= $row['contrat'] ?></div>
<div class="info-row"><b>Grade :</b> <?= $row['grade'] ?></div>
<div class="info-row"><b>Niveau d'étude :</b> <?= $row['niveau_etude'] ?></div>
<div class="info-row"><b>Domaine :</b> <?= $row['domaine'] ?></div>
<div class="info-row"><b>Situation matrimoniale :</b> <?= $row['situation_matrimoniale'] ?></div>
</div>

</div>

<!-- Script js pour exporter en PDF -->
<!-- Ajoute ces scripts juste avant </body> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
function exportPDF() {
    const { jsPDF } = window.jspdf;
    const container = document.querySelector(".container");

    html2canvas(container, { scale: 2 }).then(canvas => {
        const imgData = canvas.toDataURL("image/png");
        const pdf = new jsPDF('p', 'pt', 'a4');
        const imgProps = pdf.getImageProperties(imgData);
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
        pdf.save("employe_<?= $row['matricule'] ?>.pdf");
    });
}
</script>

</body>
</html>