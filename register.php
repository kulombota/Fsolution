<?php
session_start();
$message = '';

if(isset($_POST['register'])){

    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $password  = $_POST['password'];
    $role      = $_POST['role'];

    $conn = new mysqli("localhost", "root", "", "personnel");
    if ($conn->connect_error) die("Erreur : " . $conn->connect_error);

    // Vérification champs
    if(empty($full_name) || empty($username) || empty($password) || empty($role)){
        $message = "Tous les champs sont obligatoires";
    } else {

        // Vérifier utilisateur existant
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            $message = "Nom d'utilisateur déjà pris";
        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $password_hash, $full_name, $role);

            if($stmt->execute()){
                $message = "Compte créé avec succès ! <a href='connexion.php' style='color:#fff;text-decoration:underline;'>Se connecter</a>";
            } else {
                $message = "Erreur lors de l'inscription";
            }
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Créer un compte</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
* { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }
body, html { height:100%; display:flex; justify-content:center; align-items:center; background: linear-gradient(135deg,#2563eb,#1e40af); }
.register-container {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(12px);
    padding: 40px 30px;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    width: 380px;
    color: #fff;
    text-align: center;
    animation: fadeIn 0.6s ease;
}
.register-container h2 { margin-bottom: 25px; font-size:26px; font-weight:600; }
.register-container input {
    width: 100%;
    padding: 12px 15px;
    margin: 10px 0;
    border-radius: 12px;
    border: none;
    outline: none;
    font-size: 15px;
    background: rgba(255,255,255,0.2);
    color: #fff;
}
.register-container select {
    width: 100%;
    padding: 12px 15px;
    margin: 10px 0;
    border-radius: 12px;
    border: none;
    outline: none;
    font-size: 15px;
    background:rgba(14, 13, 13, 0.2);
    color: #f3eeeeff;
}
.register-container input::placeholder { color:#e0e0e0; }
.register-container button {
    width:100%;
    padding:12px;
    margin-top:15px;
    border:none;
    border-radius:12px;
    background:#2563eb;
    font-size:16px;
    font-weight:600;
    color:#fff;
    cursor:pointer;
    transition:0.3s;
}
.register-container button:hover { background:#1e4fc1; transform: translateY(-2px); }
.error-message {
    margin-top:10px;
    background: rgba(255,50,50,0.2);
    padding:8px 10px;
    border-radius:10px;
    color:#ff3b3b;
    font-weight:500;
}
.success-message {
    margin-top:10px;
    background: rgba(0,200,80,0.2);
    padding:8px 10px;
    border-radius:10px;
    color:#0aff6a;
    font-weight:500;
}
@keyframes fadeIn { from{opacity:0; transform:translateY(15px);} to{opacity:1; transform:translateY(0);} }
</style>
</head>
<body>

<div class="register-container">
    <h2>📝 Créer un compte</h2>
    <?php 
        if($message){
            $cls = strpos($message,'succès')!==false ? 'success-message' : 'error-message';
            echo "<div class='$cls'>$message</div>";
        }
    ?>
    <form method="POST">
        <input type="text" name="full_name" placeholder="Nom complet" required>
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <select name="role" required>
            <option value="">Sélectionner un rôle </option>
            <option value="DG">Direction Générale</option>
            <option value="RH">Réssources Humaines</option>
            <option value="DP">Direction Provinciale</option>
                   </select>
        <button type="submit" name="register">Créer un compte</button>
    </form>
    <p style="margin-top:12px;font-size:14px;">Déjà un compte ? <a href="connexion.php" style="color:#fff;text-decoration:underline;">Se connecter</a></p>
</div>

</body>
</html>
