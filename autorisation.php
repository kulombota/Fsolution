<?php
$conn = new mysqli("localhost","root","","personnel");
$directions = $conn->query("SELECT * FROM directions");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Autorisation de soins</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #f4f6f9;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100%;
            background: #0d6efd;
            color: white;
            padding-top: 20px;
        }

        .sidebar h4 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.2);
            padding-left: 25px;
        }

        /* CONTENU */
        .content {
            margin-left: 220px;
            padding: 20px;
        }

        .card {
            border-radius: 10px;
        }
    </style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <h4>🏥 RH Système</h4>

    <a href="autorisation.php">📝 Nouvelle demande</a>
    <a href="liste_autorisations.php">📄 Liste des autorisations</a>
    <a href="#">🏢 Directions</a>
    <a href="#">👨‍💼 Agents</a>
    <a href="#">📊 Statistiques</a>
     <a href="#">⚙️ Paramètres</a>
    <a href="index.php">Quitter</a>

</div>

<!-- CONTENU -->
<div class="content">

<div class="container mt-3">

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>📝 Formulaire d'autorisation de soins</h4>
        </div>

        <div class="card-body">

            <form method="POST" action="traitement_autorisation.php">

                <div class="row">
                    <div class="col-md-4">
                        <label>Nom</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label>Postnom</label>
                        <input type="text" name="postnom" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label>Prénom</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label>Matricule</label>
                        <input type="text" name="matricule" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label>Direction</label>
                        <select name="direction_id" class="form-select" required>
                            <option value="">-- Choisir --</option>
                            <?php while($d = $directions->fetch_assoc()): ?>
                                <option value="<?= $d['id'] ?>">
                                    <?= $d['nom_direction'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Fonction</label>
                        <input type="text" name="fonction" class="form-control">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label>Téléphone</label>
                        <input type="text" name="telephone" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>Hôpital</label>
                        <input type="text" name="hopital" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label>Date consultation</label>
                        <input type="date" name="date_consultation" class="form-control" required>
                    </div>
                </div>

                <div class="mt-3">
                    <label>Motif</label>
                    <textarea name="motif" class="form-control" rows="3" required></textarea>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <button type="submit" class="btn btn-success">💾 Enregistrer</button>
                    <button type="reset" class="btn btn-secondary">❌ Annuler</button>
                    <a href="liste_autorisations.php" class="btn btn-info">📄 Voir la liste</a>
                </div>

            </form>

        </div>
    </div>

</div>

</div>

</body>
</html>