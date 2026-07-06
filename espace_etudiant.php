<?php
session_start();
require_once 'config.php';

// Sécurité : Vérifier si l'étudiant est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: connexion.php");
    exit();
}

$search = "";
$limit_clause = "LIMIT 4"; // Par défaut, on limite à 4 livres comme sur l'interface

// Fonctionnalité : "Voir tout"
if (isset($_GET['voir_tout'])) {
    $limit_clause = ""; // Enlève la limite pour tout afficher
}

// Fonctionnalité : "Rechercher un livre ou un auteur" (recherche ou acteur selon le placeholder du Figma)
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    // On recherche dans le titre OU dans l'auteur
    $stmt = $pdo->prepare("SELECT * FROM livres WHERE titre LIKE ? OR auteur LIKE ? $limit_clause");
    $stmt->execute(["%$search%", "%$search%"]);
    $livres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Affichage par défaut
    $stmt = $pdo->query("SELECT * FROM livres $limit_clause");
    $livres = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Étudiant - Bibliothèque Numérique</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #b08bb6; /* Couleur violette/mauve fidèle à ton Figma */
            padding: 30px;
            color: #000000;
        }

        /* En-tête de la page */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .logo-box {
            background-color: #ffffff;
            padding: 10px 20px;
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .logo-box i {
            color: #1e3a8a;
            font-size: 30px;
        }

        .logo-box span {
            font-size: 10px;
            font-weight: 700;
            color: #1e3a8a;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .main-title {
            font-size: 36px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-logout {
            background-color: #ff4d4d;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-logout:hover {
            background-color: #cc0000;
        }

        /* Section Bienvenue et Recherche */
        .welcome-section {
            position: relative;
            margin-bottom: 40px;
        }

        .welcome-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .welcome-subtitle {
            font-size: 16px;
            margin-bottom: 25px;
        }

        /* Formulaire de Recherche */
        .search-form {
            max-width: 650px;
        }

        .search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-wrapper i {
            position: absolute;
            left: 15px;
            color: #333333;
            font-size: 18px;
        }

        .search-wrapper input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            font-size: 16px;
            border: 1px solid #000000;
            outline: none;
            background-color: #ffffff;
        }

        /* Illustration pile de livres à droite */
        .illustration-books {
            position: absolute;
            right: 40px;
            top: -20px;
            width: 150px;
        }

        /* Section Liste des Livres */
        .books-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1100px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
        }

        .link-see-all {
            color: #0000ee;
            text-decoration: underline;
            font-size: 14px;
            font-weight: 500;
        }

        /* Grille des livres */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
            max-width: 1100px;
        }

        .book-card {
            background-color: #c99ecf; /* Fond rose/mauve plus clair pour détacher la carte */
            border: 1px solid rgba(0,0,0,0.1);
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .book-cover-container {
            width: 100%;
            height: 220px;
            background-color: #ffffff;
            margin-bottom: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            border: 1px solid #777;
        }

        .book-cover-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .book-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 12px;
            min-height: 40px; /* Évite les décalages si le titre prend 2 lignes */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Bouton Fonctionnalité : Lire PDF */
        .btn-read-pdf {
            width: 100%;
            background-color: #ffffff;
            color: #333333;
            border: 1px solid #000000;
            padding: 8px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .btn-read-pdf:hover {
            background-color: #eeeeee;
        }

        .no-results {
            grid-column: 1 / -1;
            background-color: rgba(255,255,255,0.2);
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="header-container">
        <div class="logo-box">
            <i class="fa-solid fa-book-open"></i>
            <span>BIBLIONET</span>
        </div>
        <div class="main-title">Espace Étudiant</div>
        <a href="deconnexion.php" class="btn-logout">
            <i class="fa-solid fa-right-from-bracket"></i> Se déconnecter
        </a>
    </div>

    <div class="welcome-section">
        <h2 class="welcome-title">Bienvenue !</h2>
        <p class="welcome-subtitle">Recherchez, consultez et téléchargez vos documents</p>
        
        <form action="espace_etudiant.php" method="GET" class="search-form">
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" placeholder="Rechercher un livre ou un acteur..." value="<?php echo htmlspecialchars($search); ?>" onchange="this.form.submit()">
            </div>
        </form>

        <div class="illustration-books">
            <i class="fa-solid fa-book-bookmark" style="font-size: 90px; color: #4a285a; opacity: 0.7;"></i>
        </div>
    </div>

    <div class="books-section-header">
        <h3 class="section-title">Livres disponibles</h3>
        <a href="espace_etudiant.php?voir_tout=1" class="link-see-all">Voir tout</a>
    </div>

    <div class="books-grid">
        <?php if (count($livres) > 0): ?>
            <?php foreach ($livres as $livre): ?>
                <div class="book-card">
                    <div class="book-cover-container">
                        <?php if (!empty($livre['couverture']) && file_exists("uploads/" . $livre['couverture'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($livre['couverture']); ?>" alt="Couverture">
                        <?php else: ?>
                            <i class="fa-regular fa-image" style="font-size: 50px; color: #777;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="book-title">
                        <?php echo htmlspecialchars($livre['titre']); ?>
                    </div>
                    
                    <a href="uploads/<?php echo htmlspecialchars($livre['fichier_pdf']); ?>" target="_blank" class="btn-read-pdf">
                        <i class="fa-solid fa-file-pdf"></i> Lire/Télécharger PDF
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">Aucun livre trouvé pour votre recherche.</div>
        <?php endif; ?>
    </div>

</body>
</html>