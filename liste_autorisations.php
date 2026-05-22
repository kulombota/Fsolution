<?php
$conn = new mysqli("localhost","root","","personnel");

$sql = "SELECT a.*, d.nom_direction 
        FROM autorisation_soins a
        JOIN directions d ON d.id = a.direction_id
        ORDER BY a.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Liste</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="d-flex justify-content-between mb-3">
        <h3>📋 Liste des autorisations</h3>
        <a href="autorisation.php" class="btn btn-primary">➕ Nouvelle demande</a>
    </div>

    <table class="table table-bordered table-hover bg-white">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Agent</th>
                <th>Direction</th>
                <th>Hôpital</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['nom']." ".$row['prenom'] ?></td>
                <td><?= $row['nom_direction'] ?></td>
                <td><?= $row['hopital'] ?></td>
                <td><?= $row['date_consultation'] ?></td>

                <td>
                    <a href="imprime_autorisation.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">🖨️</a>
                    <a href="autorisation.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">✏️</a>
                    <a href="supprimer_autorisation.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Supprimer ?')">🗑️</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</div>

</body>
</html>