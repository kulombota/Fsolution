<?php
session_start();
$conn = new mysqli("localhost","root","","personnel");

$id = $_SESSION['user_id'];

$sql = "SELECT * FROM demandes_conges WHERE employe_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Mes demandes de congé</h2>

<table border="1" cellpadding="10">
<tr>
    <th>Date début</th>
    <th>Date fin</th>
    <th>Statut</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['date_debut'] ?></td>
    <td><?= $row['date_fin'] ?></td>
    <td><?= $row['statut'] ?></td>
</tr>
<?php endwhile; ?>

</table>