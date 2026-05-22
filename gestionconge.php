<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: connexion.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost","root","","personnel");
if($conn->connect_error) die("Erreur DB : ".$conn->connect_error);

// Liste des mois pour le filtre
$mois_list = [
    '01'=>"Janvier",'02'=>"Février",'03'=>"Mars",'04'=>"Avril",
    '05'=>"Mai",'06'=>"Juin",'07'=>"Juillet",'08'=>"Août",
    '09'=>"Septembre",'10'=>"Octobre",'11'=>"Novembre",'12'=>"Décembre"
];

$filtre_mois = intval($_GET['mois'] ?? date('m'));

// Récupérer agents avec congé ce mois basé sur date_embauche
$sql = "SELECT e.id, e.nom, e.postnom, e.prenom, e.direction, e.fonction, e.date_embauche,
               c.date_conge, c.deja_conge
        FROM employes e
        LEFT JOIN conges c ON c.employe_id = e.id AND MONTH(c.date_conge) = ?
        WHERE e.user_id = ?
        ORDER BY e.nom ASC";

$stmt = $conn->prepare($sql);
if(!$stmt) die("Erreur préparation SQL : " . $conn->error);

$stmt->bind_param("ii", $filtre_mois, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$agents = $result->fetch_all(MYSQLI_ASSOC);

// Statistiques
$total_agents = count($agents);
$agents_conge = count(array_filter($agents, fn($a)=>$a['deja_conge']==1));
$agents_non_conge = $total_agents - $agents_conge;

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des congés</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

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
.stats-box{margin-bottom:15px;}
</style>
</head>
<body>
<div class="container">
<h1>📅 Gestion des congés des agents</h1>

<!-- Statistiques -->
<div class="row stats-box text-center">
    <div class="col-md-4"><div class="alert alert-primary">Total Agents : <?= $total_agents ?></div></div>
    <div class="col-md-4"><div class="alert alert-success">En Congé : <?= $agents_conge ?></div></div>
    <div class="col-md-4"><div class="alert alert-danger">Non Congé : <?= $agents_non_conge ?></div></div>
</div>

<!-- Boutons supérieurs (actions globales) -->
<div class="top-buttons">
    <a href="employé.php" class="btn btn-success btn-lg btn-custom">➕ Ajouter</a>
    <button class="btn btn-primary btn-lg btn-custom" onclick="window.print()">🖨 Imprimer</button>
    <button class="btn btn-warning btn-lg btn-custom" id="exportPDF">📄 Export PDF</button>
    <button class="btn btn-info btn-lg btn-custom" id="exportExcel">📊 Export Excel</button>
   
    <button class="btn btn-danger btn-lg btn-custom" onclick="$('#selectAll').prop('checked', false);location.reload()">🔄 Rafraîchir</button>

</div>

<!-- Filtre par mois -->
<div class="d-flex justify-content-end mb-2">
<form method="get" class="d-flex">
    <label class="me-2">Filtrer par mois :</label>
    <select name="mois" class="form-select me-2">
        <?php foreach($mois_list as $num=>$mois): ?>
            <option value="<?= $num ?>" <?= $num==$filtre_mois?'selected':'' ?>><?= $mois ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-success">Filtrer</button>
</form>
</div>

<!-- Tableau des agents -->
<form method="post" id="formActions">
<table id="congesTable" class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>ID</th>
            <th>Nom Complet</th>
            <th>Direction</th>
            <th>Fonction</th>
            <th>Date Embauche</th>
            <th>Date Congé</th>
            <th>Déjà Congé</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($agents as $a): ?>
        <tr>
            <td><input type="checkbox" name="agent_ids[]" value="<?= $a['id'] ?>"></td>
            <td><?= $a['id'] ?></td>
            <td><?= $a['nom'].' '.$a['postnom'].' '.$a['prenom'] ?></td>
            <td><?= $a['direction'] ?></td>
            <td><?= $a['fonction'] ?></td>
            <td><?= $a['date_embauche'] ?></td>
            <td><?= $a['date_conge'] ?? '-' ?></td>
            <td>
                <?php if($a['deja_conge']==1): ?>
                    <span class="status-conge status-oui">Oui</span>
                <?php else: ?>
                    <span class="status-conge status-non">Non</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if($a['deja_conge']==0): ?>
                    <button type="button" class="btn btn-sm btn-success give-conge" data-id="<?= $a['id'] ?>">Donner Congé</button>
                <?php else: ?>
                    <button type="button" class="btn btn-sm btn-danger cancel-conge" data-id="<?= $a['id'] ?>">Annuler Congé</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Actions groupées -->
<div class="mb-3">
    <button type="button" class="btn btn-success" id="giveSelected">Donner congé sélectionnés</button>
    <button type="button" class="btn btn-danger" id="cancelSelected">Annuler congé sélectionnés</button>
    <a href="index.php" class="forgot">Retour</a>
</div>
</form>
</div>

<script>
$(document).ready(function(){
    $('#congesTable').DataTable({
        "language": {
            "search": "🔍 Rechercher :",
            "lengthMenu": "Afficher _MENU_ agents",
            "info": "Affichage _START_ à _END_ sur _TOTAL_ agents",
            "paginate": {"first":"Premier","last":"Dernier","next":"Suivant","previous":"Précédent"}
        }
    });

    // Sélection globale
    $('#selectAll').click(function(){
        $('input[name="agent_ids[]"]').prop('checked', this.checked);
    });

    // Actions individuelles
    $('.give-conge').click(function(){
        let id = $(this).data('id');
        if(confirm("Donner congé à cet agent ?")){
            window.location.href = "action_conge.php?action=give&id="+id;
        }
    });

    $('.cancel-conge').click(function(){
        let id = $(this).data('id');
        if(confirm("Annuler congé de cet agent ?")){
            window.location.href = "action_conge.php?action=cancel&id="+id;
        }
    });

    // Actions groupées
    $('#giveSelected').click(function(){
        let ids = $('input[name="agent_ids[]"]:checked').map(function(){ return this.value; }).get();
        if(ids.length && confirm("Donner congé aux agents sélectionnés ?")){
            window.location.href = "action_conge.php?action=give&ids="+ids.join(",");
        }
    });

    $('#cancelSelected').click(function(){
        let ids = $('input[name="agent_ids[]"]:checked').map(function(){ return this.value; }).get();
        if(ids.length && confirm("Annuler congé aux agents sélectionnés ?")){
            window.location.href = "action_conge.php?action=cancel&ids="+ids.join(",");
        }
    });


    // Export PDF
    $('#exportPDF').click(function(){
        const { jsPDF } = window.jspdf;
        var doc = new jsPDF('p', 'pt', 'a4');
        var headers = [];
        $('#employesTable thead tr th').each(function(i){
            if(i!==0) headers.push($(this).text());
        });
        var data = [];
        $('#employesTable tbody tr:visible').each(function(){
            var rowData = [];
            $(this).find('td').each(function(i){
                if(i!==0) rowData.push($(this).text().trim());
            });
            data.push(rowData);
        });
        doc.autoTable({
            head:[headers],
            body:data,
            startY:40,
            theme:'grid',
            headStyles:{fillColor:[52,58,64],textColor:255},
            styles:{fontSize:10}
        });
        doc.save('employes.pdf');
    });

    // Export Excel
    $('#exportExcel').click(function(){
        var wb = XLSX.utils.table_to_book(document.getElementById('employesTable'), {sheet:"Employés"});
        XLSX.writeFile(wb,"employes.xlsx");
    });
});


</script>
</body>
</html>