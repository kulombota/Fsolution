<?php
session_start();

// Sécurité : seul admin peut accéder
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "personnel";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) die("Erreur de connexion : ".$conn->connect_error);

$message = '';
$generatedPassword = '';

// Supprimer un utilisateur
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id=$id");
    $message = "Utilisateur supprimé avec succès !";
}

// Modifier un utilisateur
if(isset($_POST['edit_user'])){
    $id = intval($_POST['id']);
    $username = $conn->real_escape_string($_POST['username']);
    $role = $conn->real_escape_string($_POST['role']);
    $password = $_POST['password'];
    $sql = "UPDATE users SET username='$username', role='$role'";
    if(!empty($password)){
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password_hash='$password_hash'";
    }
    $sql .= " WHERE id=$id";
    if($conn->query($sql)) $message = "Utilisateur modifié avec succès !";
    else $message = "Erreur : ".$conn->error;
}

// Création d’un utilisateur
if(isset($_POST['add_user'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $role = $conn->real_escape_string($_POST['role']);
    $password = $_POST['password'];

    if(empty($password)){
        $password = bin2hex(random_bytes(4)); // 8 caractères
    }
    $generatedPassword = $password;
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password_hash, role) VALUES ('$username','$password_hash','$role')";
    if($conn->query($sql)) {
        $message = "Utilisateur ajouté avec succès ! Mot de passe : <strong>$generatedPassword</strong>";
    } else {
        $message = "Erreur : ".$conn->error;
    }
}

// Liste des utilisateurs
$users = $conn->query("SELECT id, username, role FROM users ORDER BY role, username");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
body, html { margin:0; padding:0; font-family: Arial, sans-serif; height:100%; background:#f0f4f8; }
.container { display:flex; height:100vh; }
.sidebar { width: 220px; background: #1e40af; color:#fff; display:flex; flex-direction:column; padding-top:30px; }
.sidebar h2 { text-align:center; margin-bottom:40px; font-size:22px; }
.sidebar a { padding:15px 20px; text-decoration:none; color:#fff; font-weight:bold; transition:0.2s; margin:5px 10px; border-radius:10px; }
.sidebar a:hover { background:#2563eb; }
.main { flex:1; padding:20px; overflow-y:auto; }
form { background:#fff; padding:20px; border-radius:12px; box-shadow:0 5px 15px rgba(0,0,0,0.1); margin-bottom:20px; }
form input, form select { padding:10px; margin:5px 0; width:100%; border-radius:8px; border:1px solid #ccc; }
form button { padding:12px 20px; background:#2563eb; color:#fff; border:none; border-radius:10px; cursor:pointer; margin-top:10px; }
form button:hover { background:#1e3a8a; }
.message { margin:10px 0; color:green; font-weight:bold; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
table th, table td { padding:12px; text-align:left; border-bottom:1px solid #eee; }
table th { background:#2563eb; color:#fff; }
.action-btn { padding:5px 10px; margin-right:5px; border:none; border-radius:6px; cursor:pointer; color:#fff; }
.edit-btn { background:#f59e0b; }
.edit-btn:hover { background:#b45309; }
.delete-btn { background:#ef4444; }
.delete-btn:hover { background:#b91c1c; }
</style>
</head>
<body>

<div class="container">

    <div class="sidebar">
        <h2>Admin Menu</h2>
        <a href="admin_dashboard.php">🏠 Dashboard</a>
        <a href="admin_dashboard.php">➕ Ajouter Utilisateur</a>
        <a href="admin_dashboard.php">📋 Liste Agents</a>
        <a href="login.php">🚪 Déconnexion</a>
    </div>

    <div class="main">
        <h2>Ajouter un utilisateur</h2>
        <?php if($message) echo "<div class='message'>$message</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="text" name="password" placeholder="Mot de passe (laisser vide pour générer automatiquement)">
            <select name="role" required>
                <option value="">-- Sélectionner un rôle --</option>
                <option value="superviseur">Superviseur</option>
                <option value="chef_equipe">Chef d'équipe</option>
                <option value="enqueteur">Enquêteur</option>
            </select>
            <button type="submit" name="add_user">Ajouter</button>
        </form>

        <h2>Liste des utilisateurs</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
            <?php while($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= $row['role'] ?></td>
                    <td>
                        <form style="display:inline;" method="POST">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>" required>
                            <select name="role" required>
                                <option value="Supervisuer" <?= $row['role']=='Superviseur'?'selected':'' ?>>Superviseur</option>
                                <option value="chef_equipe" <?= $row['role']=='chef_equipe'?'selected':'' ?>>Chef d'équipe</option>
                                <option value="enqueteur" <?= $row['role']=='enqueteur'?'selected':'' ?>>Enquêteur</option>
                            </select>
                            <input type="text" name="password" placeholder="Nouveau mot de passe">
                            <button type="submit" name="edit_user" class="action-btn edit-btn">Modifier</button>
                        </form>
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')" class="action-btn delete-btn">Supprimer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</div>

</body>
</html>
