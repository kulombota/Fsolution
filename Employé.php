<?php
session_start();

// 🔐 Sécurité : vérifier connexion
if(!isset($_SESSION['user_id'])){
    header("Location: connexion.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Affichage des erreurs pour debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion DB
$conn = new mysqli("localhost", "root", "", "personnel");
if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

// Fonction upload sécurisée
function uploadFile($file){
    if(isset($file) && $file['error'] === 0){
        // Types autorisés
        $allowed = ['image/jpeg','image/png','image/jpg','application/pdf'];
        if(!in_array($file['type'], $allowed)){
            return null; // type non autorisé
        }

        $dossier = "uploads/";
        if(!is_dir($dossier)){
            mkdir($dossier, 0777, true);
        }
        $nomFichier = time() . "_" . basename($file['name']);
        $chemin = $dossier . $nomFichier;

        if(move_uploaded_file($file['tmp_name'], $chemin)){
            return $chemin;
        }
    }
    return null;
}

// Messages
$successMessage = "";
$errorMessage = "";

// Traitement formulaire
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'oui') {

    // Upload fichiers
    $piece_identite = uploadFile($_FILES['piece_identite'] ?? null);
    $acte_engagement = uploadFile($_FILES['acte_engagement'] ?? null);
    $diplome = uploadFile($_FILES['diplome'] ?? null);
    $cv = uploadFile($_FILES['cv'] ?? null);
    $photo = uploadFile($_FILES['photo'] ?? null); // ✅ corrigé

    // Données
    $nom = $_POST['nom'] ?? '';
    $postnom = $_POST['postnom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $sexe = $_POST['sexe'] ?? '';
    $naissance = $_POST['naissance'] ?? '';
    $matricule = $_POST['matricule'] ?? '';
    $telephone = $_POST['telephone'] ?? '';

    $direction = $_POST['direction'] ?? '';
    $division = $_POST['division'] ?? '';
    $bureau = $_POST['bureau'] ?? '';
    $fonction = $_POST['fonction'] ?? '';

    $type_piece = $_POST['type_piece'] ?? '';

    $recrutement = $_POST['recrutement'] ?? '';
    $contrat = $_POST['contrat'] ?? '';
    $grade = $_POST['grade'] ?? '';
    $niveau = $_POST['NiveauEtude'] ?? '';
    $domaine = $_POST['Dommaine'] ?? '';
    $situation = $_POST['situation'] ?? '';

    // Préparation SQL
    $stmt = $conn->prepare("
        INSERT INTO employes (
            nom, postnom, prenom, sexe, date_naissance, matricule, telephone,
            direction, division, bureau, fonction,
            type_piece, piece_identite, acte_engagement, diplome, cv, photo,
            date_embauche, contrat, grade, niveau_etude, domaine, situation_matrimoniale,
            user_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if(!$stmt){
        die("Erreur préparation : " . $conn->error);
    }

    $stmt->bind_param("sssssssssssssssssssssssi",
        $nom, $postnom, $prenom, $sexe, $naissance, $matricule, $telephone,
        $direction, $division, $bureau, $fonction,
        $type_piece, $piece_identite, $acte_engagement, $diplome, $cv, $photo,
        $recrutement, $contrat, $grade, $niveau, $domaine, $situation,
        $user_id
    );

    if($stmt->execute()){
        $successMessage = "✅ Agent enregistré avec succès !";
    } else {
        $errorMessage = "❌ Erreur : " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un nouvel agent</title>
    <link rel="stylesheet" href="AGENT2.css">
    <script>
        function confirmerEnregistrement(event){
            event.preventDefault();
            let confirmation = confirm("⚠️ Voulez-vous vraiment enregistrer cet agent ?");
            if(confirmation){
                let inputConfirm = document.createElement("input");
                inputConfirm.type = "hidden";
                inputConfirm.name = "confirm";
                inputConfirm.value = "oui";
                event.target.appendChild(inputConfirm);
                event.target.submit();
            }
        }
    </script>
</head>
<body>
    <h1>🧑‍💼 Ajouter un nouvel agent</h1>

    <?php if($successMessage): ?>
        <p style="color:green;font-weight:bold;text-align:center;"><?= $successMessage ?></p>
    <?php endif; ?>
    <?php if($errorMessage): ?>
        <p style="color:red;font-weight:bold;text-align:center;"><?= $errorMessage ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" onsubmit="confirmerEnregistrement(event)">

        <!-- Section 1 : Identification -->
        <fieldset class="section section-blue">
            <legend>1️⃣ Identification de l’agent</legend>
            <label>Nom :</label><input type="text" name="nom" required><br>
            <label>Post-Nom :</label><input type="text" name="postnom" required><br>
            <label>Prénom :</label><input type="text" name="prenom" required><br>
            <label>Sexe :</label>
            <select name="sexe">
                <option value="">Sélectionner</option>
                <option value="M">Masculin</option>
                <option value="F">Féminin</option>
            </select><br>
            <label>Date de naissance :</label><input type="date" name="naissance"><br>
            <label>Matricule :</label><input type="text" name="matricule"><br>
            <label>Téléphone :</label><input type="text" name="telephone"><br>
        </fieldset>

        <!-- Section 2 : Affectation -->
        <fieldset class="section section-green">
            <legend>2️⃣ Affectation</legend>
            <label>Direction :</label><input type="text" name="direction"><br>
            <label>Division :</label><input type="text" name="division"><br>
            <label>Bureau :</label><input type="text" name="bureau"><br>
            <label>Fonction :</label><input type="text" name="fonction"><br>
        </fieldset>

        <!-- Section 3 : Dossier -->
        <fieldset class="section section-orange">
            <legend>3️⃣ Dossier de l’agent</legend>
            <label>Type de pièce :</label>
            <select name="type_piece">
                <option value="">Choisir une pièce d'identité</option>
                <option value="CNI">Carte Nationale</option>
                <option value="Passeport">Passeport</option>
                <option value="Permis">Permis de conduire</option>
            </select>
            <input type="file" name="piece_identite"><br>

            <label>Acte d’engagement :</label><input type="file" name="acte_engagement"><br>
            <label>Diplôme :</label><input type="file" name="diplome"><br>
            <label>CV :</label><input type="file" name="cv"><br>
            <label>Photo Agent :</label><input type="file" name="photo"><br>
        </fieldset>

        <!-- Section 4 : Informations RH -->
        <fieldset class="section section-purple">
            <legend>4️⃣ Informations RH</legend>
            <label>Date d'Embauche :</label><input type="date" name="recrutement"><br>
            <label>Type de contrat :</label>
            <select name="contrat">
                <option value="">Sélectionner</option>
                <option value="CDI">CDI</option>
                <option value="CDD">CDD</option>
                <option value="Stage">Stage</option>
            </select><br>
            <label>Grade :</label><input type="text" name="grade"><br>
            <label>Niveau d'Etude :</label>
            <select name="NiveauEtude">
                <option value="">Sélectionner</option>
                <option value="D6">Secondaire</option>
                <option value="G3">Graduat</option>
                <option value="L2">Licencié(e)</option>
                <option value="DA">Master</option>
                <option value="Dr">Doctorat</option>
            </select><br>
            <label>Domaine :</label><input type="text" name="Dommaine"><br>
            <label>Situation Matrimoniale :</label>
            <select name="situation">
                <option value="">Sélectionner</option>
                <option value="Célibataire">Célibataire</option>
                <option value="Marié(e)">Marié(e)</option>
                <option value="Divorcé(e)">Divorcé(e)</option>
            </select><br>
        </fieldset>
       

        <div class="submit-container">
            <button type="submit">✅ Enregistrer</button>
              <a href="index.php" class="forgot">Retour</a>
        </div>
    </form>
</body>
</html>