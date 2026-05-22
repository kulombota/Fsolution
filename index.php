<?php
session_start();

if(!isset($_SESSION['username'])){
    header("Location: connexion.php");
    exit;
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>RH Dashboard</title>

<style>

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
}

/* SIDEBAR */
.sidebar{
    position:fixed;
    left:0;
    top:0;
    width:230px;
    height:100%;
    background:#0d6efd;
    padding-top:20px;
    color:white;
}

.sidebar h2{
    text-align:center;
    margin-bottom:30px;
}

.sidebar a{
    display:block;
    color:white;
    text-decoration:none;
    padding:12px 20px;
    transition:0.3s;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.2);
    padding-left:25px;
}

/* CONTENU DROITE */
.content{
    margin-left:230px;
    padding:20px;
}

/* HEADER */
.header{
    background:white;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}

/* DASHBOARD GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:15px;
}

.card{
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
    text-align:center;
    font-weight:bold;
    transition:0.3s;
}

.card:hover{
    transform:scale(1.05);
}

.blue{border-left:5px solid #0d6efd;}
.green{border-left:5px solid green;}
.orange{border-left:5px solid orange;}
.red{border-left:5px solid red;}

</style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>🏢 RH SYSTEM</h2>

    <a href="index.php">📊 Dashboard</a>
    <a href="Employé.php">➕ Ajouter Employé</a>
    <a href="listeagent.php">📋 Liste Employés</a>
    <a href="gerer_employes.php">🏥 Gestion employés</a>
    <a href="gestionconge.php">🏖️ Congés</a>
    <a href="statistiques.php">📊 Statistiques</a>
    <a href="logout.php">🚪 Déconnexion</a>
</div>

<!-- CONTENU DROITE -->
<div class="content">

    <!-- HEADER -->
    <div class="header">
        <h2>👋 Bienvenue, <?= $username ?></h2>
        <p>Système de gestion du personnel</p>
    </div>

    <div class="grid">

    <a href="index.php" class="card blue">
        📊<br>Présence
    </a>

    <a href="Employé.php" class="card green">
        👨‍💼<br>Gérer Présence
    </a>

    <a href="demande_conge.php" class="card orange">
        🏖️<br>Congés en cours
    </a>

    <a href="Employé.php" class="card red">
        ➕<br> 
    </a>

    <a href="autorisation.php" class="card blue">
        🏥<br>Autorisations santé
    </a>

    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    
<a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="class=valider_conge.php" class="card green">
        📈<br>Valider conger
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
    <a href="statistiques.php" class="card green">
        📈<br>Statistiques RH
    </a>
   
</div>

</body>
</html>