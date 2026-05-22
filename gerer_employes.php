<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: connexion.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost","root","","personnel");
if($conn->connect_error) die("Erreur DB : ".$conn->connect_error);

// Supprimer employés sélectionnés
if(isset($_POST['delete_selected'])){
    $ids = $_POST['selected_ids'] ?? [];
    if(count($ids) > 0){
        $placeholders = implode(',', array_fill(0,count($ids),'?'));
        $types = str_repeat('i', count($ids));
        $stmt = $conn->prepare("DELETE FROM employes WHERE id IN ($placeholders) AND user_id=?");
        $types .= 'i';
        $ids[] = $user_id;
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $stmt->close();
        header("Location: gerer_employes.php");
        exit;
    }
}

// Récupérer toutes les directions pour filtre
$dirs_result = $conn->query("SELECT DISTINCT direction FROM employes WHERE user_id=$user_id AND direction!='' ORDER BY direction ASC");
$directions = [];
if($dirs_result){
    while($row = $dirs_result->fetch_assoc()){
        $directions[] = $row['direction'];
    }
}

// Récupérer tous les employés
$result = $conn->query("SELECT * FROM employes WHERE user_id=$user_id ORDER BY id DESC");
$employes = [];
if($result){
    while($row = $result->fetch_assoc()){
        $employes[] = $row;
    }
}

// Statistiques
$stats = [];
$stats['hommes'] = $conn->query("SELECT COUNT(*) as c FROM employes WHERE user_id=$user_id AND sexe='M'")->fetch_assoc()['c'];
$stats['femmes'] = $conn->query("SELECT COUNT(*) as c FROM employes WHERE user_id=$user_id AND sexe='F'")->fetch_assoc()['c'];
$stats['niveaux'] = $conn->query("SELECT niveau_etude, COUNT(*) as c FROM employes WHERE user_id=$user_id AND niveau_etude!='' GROUP BY niveau_etude")->fetch_all(MYSQLI_ASSOC);
$stats['situation'] = $conn->query("SELECT situation_matrimoniale, COUNT(*) as c FROM employes WHERE user_id=$user_id AND situation_matrimoniale!='' GROUP BY situation_matrimoniale")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gérer mes employés</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<style>
html, body{height:100%;margin:0;background:#f0f4f8;}
.container-fluid{padding:10px 20px;height:100%;}
.btn-custom{margin:3px;font-size:1.1rem;min-width:130px;}
#stats{background:#fff;padding:20px;border-radius:10px;height:100%;overflow-y:auto;}
#stats h5{margin-top:15px;}
#table-container{background:#fff;padding:15px;border-radius:10px;height:100%;overflow:auto;}
.dataTables_wrapper .dataTables_filter {float:left !important;text-align:left !important;}
.dataTables_wrapper .dataTables_length {float:right !important;}
@media print{
    body *{visibility:hidden;}
    #printable, #printable *{visibility:visible;}
    #printable{position:absolute;top:0;left:0;width:100%;}
}
</style>
</head>
<body>
<div class="container-fluid">
<h1 class="text-center mb-4">👨‍💼 Gérer mes employés</h1>

<div class="d-flex justify-content-center flex-wrap mb-3">
    <a href="employé.php" class="btn btn-success btn-lg btn-custom">➕ Ajouter</a>
    <button class="btn btn-primary btn-lg btn-custom" onclick="window.print()">🖨 Imprimer</button>
    <button class="btn btn-warning btn-lg btn-custom" id="exportPDF">📄 Export PDF</button>
    <button class="btn btn-info btn-lg btn-custom" id="exportExcel">📊 Export Excel</button>
   
    <button class="btn btn-danger btn-lg btn-custom" onclick="$('#selectAll').prop('checked', false);location.reload()">🔄 Rafraîchir</button>
</div>

<div class="row" style="height: calc(100% - 150px);">
    <!-- Statistiques -->
    <div class="col-md-3" style="height:100%;">
        <div id="stats">
            <h4>📊 Statistiques</h4>
            <h5>Sexe :</h5>
            <p>Hommes: <?= $stats['hommes'] ?> | Femmes: <?= $stats['femmes'] ?></p>
            <h5>Niveau d'étude :</h5>
            <ul>
                <?php foreach($stats['niveaux'] as $n): ?>
                <li><?= $n['niveau_etude'] ?>: <?= $n['c'] ?></li>
                <?php endforeach; ?>
            </ul>
            <h5>Situation matrimoniale :</h5>
            <ul>
                <?php foreach($stats['situation'] as $s): ?>
                <li><?= $s['situation_matrimoniale'] ?>: <?= $s['c'] ?></li>
                <?php endforeach; ?>
            </ul>
            <h5>Filtrer par Direction :</h5>
            <select id="filterDirection" class="form-select">
                <option value="">Toutes</option>
                <?php foreach($directions as $d): ?>
                <option value="<?= $d ?>"><?= $d ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Tableau -->
    <div class="col-md-9" style="height:100%;">
        <form method="post" id="deleteForm">
            <div id="table-container">
                <div id="printable">
                    <table id="employesTable" class="table table-striped table-bordered" style="background:#fff;">
                        <thead class="table-dark">
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>ID</th>
                                <th>Nom Complet</th>
                                <th>Sexe</th>
                                <th>Matricule</th>
                                <th>Fonction</th>
                                <th>Direction</th>
                                <th>Contrat</th>
                                <th>Date Embauche</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($employes as $emp): ?>
                            <tr data-direction="<?= $emp['direction'] ?>">
                                <td><input type="checkbox" name="selected_ids[]" value="<?= $emp['id'] ?>"></td>
                                <td><?= $emp['id'] ?></td>
                                <td><?= $emp['nom'].' '.$emp['postnom'].' '.$emp['prenom'] ?></td>
                                <td><?= $emp['sexe'] ?></td>
                                <td><?= $emp['matricule'] ?></td>
                                <td><?= $emp['fonction'] ?></td>
                                <td><?= $emp['direction'] ?></td>
                                <td><?= $emp['contrat'] ?></td>
                                <td><?= $emp['date_embauche'] ?></td>
                                <td>
                                    <a href="employé.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-success btn-custom">✏️ Modifier</a>
                                    <a href="detail.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-primary btn-custom">🔍 Détails</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="delete_selected" class="btn btn-danger btn-lg btn-custom mt-2 w-100" onclick="return confirm('❌ Supprimer les employés sélectionnés ?')">🗑 Supprimer sélection</button>
                  <a href="index.php?=<?= $emp['id'] ?>" class="btn btn-sm btn-primary btn-custom">Retour</a>
            </div>
        </form>
    </div>
</div>
</div>

<script>
$(document).ready(function(){
    var table = $('#employesTable').DataTable({
        "language": {
            "search": "🔍 Rechercher :",
            "lengthMenu": "Afficher _MENU_ employés",
            "info": "Affichage _START_ à _END_ sur _TOTAL_ employés",
            "paginate": {
                "first": "Premier","last": "Dernier","next": "Suivant","previous": "Précédent"
            }
        }
    });

    $('#selectAll').click(function(){
        $('input[name="selected_ids[]"]').prop('checked', this.checked);
    });

    $('#filterDirection').change(function(){
        var val = $(this).val();
        table.rows().every(function(){
            var row = this.node();
            $(row).toggle(!val || $(row).data('direction') === val);
        });
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