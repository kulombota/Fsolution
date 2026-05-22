<?php
session_start();

// 🔐 Sécurité : vérifier connexion
if(!isset($_SESSION['user_id'])){
    header("Location: connexion.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "personnel");
if($conn->connect_error){
    die("Erreur DB : " . $conn->connect_error);
}

// 🔥 FILTRES UNIQUEMENT POUR L'UTILISATEUR CONNECTÉ
$directions = $conn->prepare("SELECT DISTINCT direction FROM employes WHERE direction != '' AND user_id = ?");
$directions->bind_param("i", $user_id);
$directions->execute();
$directions = $directions->get_result()->fetch_all(MYSQLI_ASSOC);

$divisions = $conn->prepare("SELECT DISTINCT division FROM employes WHERE division != '' AND user_id = ?");
$divisions->bind_param("i", $user_id);
$divisions->execute();
$divisions = $divisions->get_result()->fetch_all(MYSQLI_ASSOC);

$sexes = $conn->prepare("SELECT DISTINCT sexe FROM employes WHERE sexe != '' AND user_id = ?");
$sexes->bind_param("i", $user_id);
$sexes->execute();
$sexes = $sexes->get_result()->fetch_all(MYSQLI_ASSOC);

// Filtres
$search = $_GET['search'] ?? "";
$direction = $_GET['direction'] ?? "";
$division = $_GET['division'] ?? "";
$sexe = $_GET['sexe'] ?? "";

// 🔥 REQUÊTE PRINCIPALE AVEC user_id
$query = "SELECT * FROM employes WHERE user_id = ?";
$params = [$user_id];
$types = "i";

// Ajout des filtres
if(!empty($search)){
    $query .= " AND (nom LIKE ? OR matricule LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if(!empty($direction)){
    $query .= " AND direction = ?";
    $params[] = $direction;
    $types .= "s";
}

if(!empty($division)){
    $query .= " AND division = ?";
    $params[] = $division;
    $types .= "s";
}

if(!empty($sexe)){
    $query .= " AND sexe = ?";
    $params[] = $sexe;
    $types .= "s";
}

$query .= " ORDER BY id DESC";

// Préparer
$stmt = $conn->prepare($query);
if(!$stmt){
    die("Erreur SQL : " . $conn->error);
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Compteur
$total_agents = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Liste des Agents</title>
<style>
/* Styles inchangés */
body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; margin:0; }
.header { background: linear-gradient(135deg, #1e293b, #2563eb); color:white; padding:20px; text-align:center; font-size:22px; font-weight:bold; }
.container { padding:20px; max-width:1100px; margin:auto; }
.filter-box { background:white; padding:15px; border-radius:10px; box-shadow:0 3px 8px rgba(0,0,0,0.05); margin-bottom:20px; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.filter-box input, .filter-box select { padding:10px; border-radius:6px; border:1px solid #ccc; margin:5px 0; }
.counter { margin-bottom:15px; font-weight:bold; color:#1e293b; }
.card { background:white; padding:15px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08); margin-bottom:15px; display:flex; align-items:center; gap:15px; transition:0.3s; }
.card:hover { transform: translateY(-3px); }
.photo { width:70px; height:70px; border-radius:50%; object-fit:cover; border:2px solid #2563eb; }
.info { flex:1; }
.name { font-size:16px; font-weight:bold; }
.badge { background:#e0f2fe; color:#0369a1; padding:4px 10px; border-radius:20px; font-size:12px; }
.btn { padding:6px 10px; border-radius:6px; text-decoration:none; color:white; font-size:13px; margin:2px; }
.btn-view { background:#2563eb; }
.btn-doc { background:#16a34a; }
.btn-reset { background:#c50812; }
.add-btn { display:inline-block; margin-top:15px; padding:10px 15px; background:#16a34a; color:white; border-radius:8px; text-decoration:none; }
</style>
</head>
<body>

<div class="header">👥 Liste des Agents</div>
<div class="container">

<div class="counter">
Total des agents : <?= $total_agents ?>
</div>

<!-- Formulaire de recherche et filtres -->
<div class="filter-box">
<form method="GET">
    <input type="text" name="search" placeholder="🔍 Recherche par nom ou matricule" value="<?= htmlspecialchars($search) ?>">

    <select name="direction">
        <option value="">Direction</option>
        <?php foreach($directions as $d): ?>
            <option value="<?= htmlspecialchars($d['direction']) ?>" <?= $direction==$d['direction']?"selected":"" ?>><?= htmlspecialchars($d['direction']) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="division">
        <option value="">Division</option>
        <?php foreach($divisions as $div): ?>
            <option value="<?= htmlspecialchars($div['division']) ?>" <?= $division==$div['division']?"selected":"" ?>><?= htmlspecialchars($div['division']) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="sexe">
        <option value="">Sexe</option>
        <?php foreach($sexes as $s): ?>
            <option value="<?= htmlspecialchars($s['sexe']) ?>" <?= $sexe==$s['sexe']?"selected":"" ?>><?= htmlspecialchars($s['sexe']=="M"?"Masculin":"Féminin") ?></option>
        <?php endforeach; ?>
    </select>

    <button class="btn btn-view" type="submit">🔍 Rechercher</button>
    <a href="listeagent.php" class="btn btn-reset">Reset</a>
</form>
</div>

<!-- Liste des agents -->
<?php if($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <?php $photo = !empty($row['photo']) ? $row['photo'] : "uploads/default.png"; ?>
        <div class="card">
            <img class="photo" src="<?= htmlspecialchars($photo) ?>">
            <div class="info">
                <div class="name"><?= htmlspecialchars($row['nom'].' '.$row['postnom'].' '.$row['prenom']) ?></div>
                Matricule : <?= htmlspecialchars($row['matricule']) ?><br>
                <span class="badge"><?= htmlspecialchars($row['fonction']) ?></span> | <?= htmlspecialchars($row['direction'].' / '.$row['division']) ?> | Sexe : <?= htmlspecialchars($row['sexe']) ?>
            </div>
            <div>
                <a class="btn btn-view" href="detail.php?id=<?= $row['id'] ?>">👁 Voir</a>
                <?php if(!empty($row['cv'])): ?>
                    <a class="btn btn-doc" href="<?= htmlspecialchars($row['cv']) ?>" target="_blank">📄 CV</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>Aucun agent trouvé</p>
<?php endif; ?>

<a class="add-btn" href="Employé.php">➕ Ajouter un agent</a>
<a href="index.php" class="forgot">Retour</a>

</div>
</body>
</html>