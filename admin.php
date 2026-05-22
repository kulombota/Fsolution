<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'utilisateur est connecté et admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'DG'){
    header("Location: connexion.php");
    exit;
}

// Connexion à la base
$conn = new mysqli("localhost","root","","personnel");
if($conn->connect_error) die("Erreur DB : ".$conn->connect_error);

// Liste des mois pour le filtre
$mois_list = [
    '01'=>"Janvier",'02'=>"Février",'03'=>"Mars",'04'=>"Avril",
    '05'=>"Mai",'06'=>"Juin",'07'=>"Juillet",'08'=>"Août",
    '09'=>"Septembre",'10'=>"Octobre",'11'=>"Novembre",'12'=>"Décembre"
];

$filtre_mois = $_GET['mois'] ?? date('m');
$filtre_user = $_GET['user'] ?? 0;

// Vérifier et créer table conges si inexistante
$conn->query("CREATE TABLE IF NOT EXISTS conges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employe_id INT NOT NULL,
    date_conge DATE NOT NULL,
    deja_conge TINYINT(1) DEFAULT 0,
    FOREIGN KEY (employe_id) REFERENCES employes(id) ON DELETE CASCADE
)");

// Récupérer utilisateurs pour filtre
$users = $conn->query("SELECT id, username FROM users")->fetch_all(MYSQLI_ASSOC);

// Récupérer agents et congés
$sql = "SELECT e.id, e.nom, e.postnom, e.prenom, e.direction, e.fonction, e.date_embauche,
               u.username, c.date_conge, c.deja_conge
        FROM employes e
        LEFT JOIN users u ON u.id = e.user_id
        LEFT JOIN conges c ON c.employe_id = e.id AND MONTH(c.date_conge)=?
        WHERE (?=0 OR e.user_id=?)
        ORDER BY e.nom ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $filtre_mois, $filtre_user, $filtre_user);
$stmt->execute();
$result = $stmt->get_result();
$agents = $result->fetch_all(MYSQLI_ASSOC);

// Statistiques
$total_agents = count($agents);
$total_conges = count(array_filter($agents, fn($a)=>$a['deja_conge']==1));
$total_non_conge = $total_agents - $total_conges;

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Admin - Gestion des Agents & Congés</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<style>
body{background:#f5f7fa;}
.container{margin-top:20px;}
h1{text-align:center;margin-bottom:20px;}
.btn-custom{margin:3px;font-size:0.95rem;}
.table td, .table th{text-align:center; vertical-align:middle;}
.status-conge{font-weight:bold;color:white;padding:3px 8px;border-radius:6px;}
.status-oui{background:#28a745;}
.status-non{background:#dc3545;}
.top-buttons{overflow-x:auto; white-space: nowrap; margin-bottom:15px; padding-bottom:5px;}
.top-buttons .btn{display:inline-block;}
.stats{margin:10px 0; font-weight:bold;}
</style>
</head>
<body>
<div class="container">
<h1>🛡️ Admin - Gestion complète des Agents & Congés</h1>

<!-- Statistiques -->
<div class="stats d-flex justify-content-around">
    <span>Total Agents : <?= $total_agents ?></span>
    <span>Total Congés ce mois : <?= $total_conges ?></span>
    <span>Agents Non en Congé : <?= $total_non_conge ?></span>
</div>

<!-- Boutons supérieurs -->
<div class="top-buttons">
<?php
$actions = [
    "➕ Ajouter Agent","🖨 Imprimer","📄 Export PDF","📊 Export Excel","✉️ Envoyer Email",
    "⭐ Favoris","🔄 Rafraîchir","📝 Historique Congés","📅 Ajouter Congé","🗑 Supprimer",
    "🔍 Rechercher","📌 Filtrer","🧾 Rapport Mensuel","⚙️ Paramètres","🔔 Alertes",
    "📈 Statistiques","🖼 Voir Photos","💾 Sauvegarde","📂 Importer","🔒 Verrouiller",
    "👤 Profil","💡 Aide","🎯 Donner Congé","❌ Annuler Congé"
];
foreach($actions as $act) echo '<button class="btn btn-sm btn-primary btn-custom">'.$act.'</button>';
?>
</div>

<!-- Filtres -->
<div class="d-flex justify-content-start mb-2">
<form method="get" class="d-flex">
    <label class="me-2">Mois :</label>
    <select name="mois" class="form-select me-2">
        <?php foreach($mois_list as $num=>$mois): ?>
            <option value="<?= $num ?>" <?= $num==$filtre_mois?'selected':'' ?>><?= $mois ?></option>
        <?php endforeach; ?>
    </select>
    <label class="me-2">Utilisateur :</label>
    <select name="user" class="form-select me-2">
        <option value="0">Tous</option>
        <?php foreach($users as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $u['id']==$filtre_user?'selected':'' ?>><?= $u['username'] ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-success">Filtrer</button>
</form>
</div>

<!-- Tableau des agents -->
<table id="adminTable" class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>ID</th>
            <th>Nom Complet</th>
            <th>Direction</th>
            <th>Fonction</th>
            <th>Date Embauche</th>
            <th>Utilisateur</th>
            <th>Date Congé</th>
            <th>Déjà Congé</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($agents as $a): ?>
        <tr>
            <td><input type="checkbox" class="agent-checkbox" value="<?= $a['id'] ?>"></td>
            <td><?= $a['id'] ?></td>
            <td><?= $a['nom'].' '.$a['postnom'].' '.$a['prenom'] ?></td>
            <td><?= $a['direction'] ?></td>
            <td><?= $a['fonction'] ?></td>
            <td><?= $a['date_embauche'] ?></td>
            <td><?= $a['username'] ?></td>
            <td><?= $a['date_conge'] ?? '-' ?></td>
            <td>
                <?php if($a['deja_conge']==1): ?>
                    <span class="status-conge status-oui">Oui</span>
                <?php else: ?>
                    <span class="status-conge status-non">Non</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Actions de masse -->
<div class="mt-2">
    <button id="giveConge" class="btn btn-success btn-custom">🎯 Donner Congé</button>
    <button id="cancelConge" class="btn btn-danger btn-custom">❌ Annuler Congé</button>
</div>

</div>

<script>
$(document).ready(function(){
    var table = $('#adminTable').DataTable({
        "language": {
            "search": "🔍 Rechercher :",
            "lengthMenu": "Afficher _MENU_ agents",
            "info": "Affichage _START_ à _END_ sur _TOTAL_ agents",
            "paginate": {"first":"Premier","last":"Dernier","next":"Suivant","previous":"Précédent"}
        }
    });

    // Sélection globale
    $('#selectAll').click(function(){
        $('.agent-checkbox').prop('checked', this.checked);
    });

    // Fonction pour donner ou annuler congé
    function actionConge(action){
        var ids = $('.agent-checkbox:checked').map(function(){ return $(this).val(); }).get();
        if(ids.length==0){ alert("⚠️ Sélectionnez au moins un agent !"); return; }

        $.post('action_conge_admin.php', {action: action, ids: ids}, function(resp){
            alert(resp.message);
            location.reload();
        }, 'json');
    }

    $('#giveConge').click(function(){ actionConge('give'); });
    $('#cancelConge').click(function(){ actionConge('cancel'); });
});
</script>
</body>
</html>