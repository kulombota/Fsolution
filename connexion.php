<?php
session_start();

// Si déjà connecté → redirection
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$message = '';

if(isset($_POST['login'])){

    // Sécurisation des entrées
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Connexion DB
    $conn = new mysqli("localhost", "root", "", "personnel");
    if ($conn->connect_error) {
        die("Erreur connexion : " . $conn->connect_error);
    }

    // Préparation requête
    $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $user = $result->fetch_assoc();

        // Vérification mot de passe
        if(password_verify($password, $user['password_hash'])){

            // Création session
            $_SESSION['user_id'] = $user['id']; // 🔥 IMPORTANT
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirection selon rôle (facultatif)
            if($user['role'] == 'DG'){
                header("Location: admin.php");
            } elseif($user['role'] == 'RH'){
                header("Location: index.php");
            } else {
                header("Location: index.php");
            }
            exit;

        } else {
            $message = "❌ Mot de passe incorrect";
        }

    } else {
        $message = "❌ Utilisateur introuvable";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

* {
    box-sizing: border-box;
    margin:0;
    padding:0;
    font-family: 'Inter', sans-serif;
}

body, html {
    height: 100%;
    display:flex;
    justify-content:center;
    align-items:center;
    background: linear-gradient(135deg,#1e3a8a,#3b82f6);
}

.login-container {
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(15px);
    padding: 45px 35px;
    border-radius: 22px;
    box-shadow: 0 12px 35px rgba(0,0,0,0.3);
    width: 380px;
    color: #fff;
    text-align: center;
    animation: fadeIn 0.7s ease;
}

.login-container h2 {
    margin-bottom: 28px;
    font-size: 28px;
    font-weight: 600;
}

.login-container input {
    width: 100%;
    padding: 14px 16px;
    margin: 12px 0;
    border-radius: 14px;
    border: none;
    outline: none;
    font-size: 16px;
    background: rgba(255,255,255,0.2);
    color: #fff;
}

.login-container input::placeholder {
    color: #d1d5db;
}

.login-container button {
    width: 100%;
    padding: 14px;
    margin-top: 18px;
    border: none;
    border-radius: 14px;
    background: #3b82f6;
    font-size: 17px;
    font-weight: 600;
    color: #fff;
    cursor: pointer;
    transition: 0.3s;
}
div img{
    width: 130px;
    height: 90px;
    margin-left: 20px;
}

.login-container button:hover {
    background: #1e40af;
    transform: translateY(-2px);
}

.error-message {
    margin-bottom: 10px;
    background: rgba(255,50,50,0.25);
    padding: 10px 12px;
    border-radius: 12px;
    color: #ff3b3b;
    font-weight: 500;
}

.success-message {
    margin-bottom: 10px;
    background: rgba(50,255,50,0.2);
    padding: 10px 12px;
    border-radius: 12px;
    color: #22c55e;
    font-weight: 500;
}

@keyframes fadeIn {
    from { opacity:0; transform: translateY(20px); }
    to { opacity:1; transform: translateY(0); }
}

.forgot {
    margin-top: 14px;
    font-size: 14px;
    color: #d1d5db;
    text-decoration: none;
    display: block;
}

.forgot:hover {
    text-decoration: underline;
}
div Footer {
   
    color: black;
    text-align: center;
    padding: 2px;
    font-size: 12px;
    margin: 1px ;
  
}
</style>
</head>

<body>

<div class="login-container">
    <img src="1.jpg" alt="">
    <h2>🔒 Connexion</h2>

    <?php if($message): ?>
        <div class="error-message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit" name="login">Se connecter</button>
    </form>

    <a href="register.php" class="forgot">Créer un compte</a><br>
    <footer>
        <p>Application de gestion du personnel © INS 2026</p>
    </footer>
</div>

</body>
</html>