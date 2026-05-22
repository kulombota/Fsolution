<?php
$conn = new mysqli("localhost","root","","personnel");
if($conn->connect_error) die("Erreur DB");

$id = $_GET['id'];

$sql = "SELECT a.*, d.nom_direction 
        FROM autorisation_soins a
        JOIN directions d ON d.id = a.direction_id
        WHERE a.id = $id";

$result = $conn->query($sql);
$data = $result->fetch_assoc();

$num = "AUT-".date("Y")."-".$data['id'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Autorisation</title>

<style>

body{
    font-family: "Times New Roman", serif;
    background:#eaeaea;
}

/* PAGE A5 PRO */
.page{
    width: 148mm;
    min-height: 210mm;
    margin: 20px auto;
    padding: 12mm;
    background:white;
    box-shadow: 0 0 12px rgba(0,0,0,0.15);
    box-sizing: border-box;
}

/* HEADER */
.header{
    display:flex;
    align-items:center;
    border-bottom:2px solid #000;
    padding-bottom:10px;
    margin-bottom:15px;
}

/* LOGO PROPRE */
.logo{
    width:75px;
    height:75px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-right:12px;
}

.img-logo{
    max-width:70px;
    max-height:70px;
    object-fit:contain;
}

/* TEXTE HEADER */
.header-text{
    flex:1;
    text-align:center;
    font-size:12px;
    font-weight:bold;
    line-height:1.4;
}

/* TITRE */
.title{
    text-align:center;
    font-size:15px;
    font-weight:bold;
    text-decoration:underline;
    margin:10px 0 15px 0;
}

/* NUMERO */
.num{
    font-size:12px;
    margin-bottom:10px;
}

/* CONTENU */
.content{
    font-size:13px;
    line-height:1.7;
}

/* INFOS AGENT */
.info{
    margin-left:15px;
    margin-top:5px;
}

/* SIGNATURE */
.signature{
    margin-top:40px;
    display:flex;
    justify-content:space-between;
    font-size:12px;
}

/* PRINT */
.print-btn{
    position:fixed;
    bottom:20px;
    right:20px;
}

@media print{
    body{background:white;}
    .page{box-shadow:none;margin:0;}
    .print-btn{display:none;}
}

</style>

</head>

<body>

<div class="page">

    <!-- HEADER -->
    <div class="header">

        <div class="logo">
            <img src="1.jpg" class="img-logo">
        </div>

        <div class="header-text">
            RÉPUBLIQUE DÉMOCRATIQUE DU CONGO<br>
            MINISTÈRE / ORGANISATION<br>
            SERVICE DES RESSOURCES HUMAINES
        </div>

    </div>

    <!-- TITRE -->
    <div class="title">
        AUTORISATION DE SOINS
    </div>

    <!-- NUMERO -->
    <div class="num">
        <strong>N° :</strong> <?= $num ?>
    </div>

    <!-- CONTENU -->
    <div class="content">

        <p>Nous soussignés, autorisons l’agent :</p>

        <div class="info">
            <strong>
                Nom : <?= $data['nom'] ?><br>
                Postnom : <?= $data['postnom'] ?><br>
                Prénom : <?= $data['prenom'] ?><br>
                Matricule : <?= $data['matricule'] ?><br>
                Direction : <?= $data['nom_direction'] ?><br>
                Fonction : <?= $data['fonction'] ?>
            </strong>
        </div>

        <br>

        <p>
            À se rendre à l’hôpital : <strong><?= $data['hopital'] ?></strong>
        </p>

        <p>
            Date de consultation : <strong><?= $data['date_consultation'] ?></strong>
        </p>

        <p>
            Motif : <strong><?= $data['motif'] ?></strong>
        </p>

        <p>
            Fait à Kinshasa, le <?= date("d/m/Y", strtotime($data['date_demande'])) ?>
        </p>

    </div>

    <!-- SIGNATURE -->
    <div class="signature">

        <div>
            L’Agent
        </div>

        <div>
            Responsable RH<br><br>
            Signature & Cachet
        </div>

    </div>

</div>

<!-- BOUTON -->
<div class="print-btn">
    <button onclick="window.print()">🖨️ Imprimer</button>
</div>

</body>
</html>