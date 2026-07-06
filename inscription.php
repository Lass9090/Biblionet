<?php
require_once 'config.php';

$message_erreur = "";
$message_succes = "";

// Traitement du formulaire lorsqu'il est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_complet = trim($_POST['nom_complet']);
    $email = trim($_POST['email']);
    $numero_etudiant = trim($_POST['numero_etudiant']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérifications de base
    if (empty($nom_complet) || empty($email) || empty($numero_etudiant) || empty($password) || empty($confirm_password)) {
        $message_erreur = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message_erreur = "Format d'adresse e-mail invalide.";
    } elseif ($password !== $confirm_password) {
        $message_erreur = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'email ou le numéro d'étudiant existe déjà
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? OR numero_etudiant = ?");
        $stmt->execute([$email, $numero_etudiant]);
        
        if ($stmt->rowCount() > 0) {
            $message_erreur = "L'adresse e-mail ou le numéro d'étudiant est déjà utilisé.";
        } else {
            // Hachage sécurisé du mot de passe
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Insertion dans la base de données
            $insert = $pdo->prepare("INSERT INTO utilisateurs (nom_complet, email, numero_etudiant, mot_de_passe) VALUES (?, ?, ?, ?)");
            if ($insert->execute([$nom_complet, $email, $numero_etudiant, $password_hash])) {
                $message_succes = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
                // Optionnel : redirection automatique vers la page de connexion après 2 secondes
                // header("refresh:2;url=connexion.php");
            } else {
                $message_erreur = "Une erreur est survenue lors de l'inscription.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Bibliothèque Numérique</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 30px 25px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
        }

        .logo-box {
            background-color: #000000;
            width: 80px;
            height: 80px;
            margin: 0 auto 15px auto;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-box i {
            color: #3b82f6;
            font-size: 40px;
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #000000;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 13px;
            color: #666666;
            margin-bottom: 25px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #000000;
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i.field-icon {
            position: absolute;
            left: 15px;
            color: #333333;
            font-size: 18px;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            background-color: #eeeeee;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            color: #333333;
            outline: none;
        }

        .input-wrapper input::placeholder {
            color: #757575;
        }

        .input-wrapper .toggle-password {
            position: absolute;
            right: 15px;
            color: #000000;
            cursor: pointer;
            font-size: 18px;
        }

        .btn-submit {
            width: 100%;
            background-color: #2b17ff;
            color: #ffffff;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #1e0cdb;
        }

        .btn-cancel {
            width: 100%;
            background-color: #eeeeee;
            color: #333333;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
        }

        .footer-text {
            margin-top: 15px;
            font-size: 13px;
            color: #000000;
        }

        .footer-text a {
            color: #2b17ff;
            text-decoration: none;
            font-weight: 500;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        /* Styles des messages alertes */
        .alert {
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 15px;
            text-align: left;
        }
        .alert-danger {
            background-color: #fde8e8;
            color: #e53e3e;
            border: 1px solid #fca5a5;
        }
        .alert-success {
            background-color: #def7ec;
            color: #03543f;
            border: 1px solid #84e1bc;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="logo-box">
        <i class="fa-solid fa-graduation-cap"></i>
    </div>

    <h1>Inscription</h1>
    <p class="subtitle">Créer votre compte pour accéder à votre espace..</p>

    <?php if(!empty($message_erreur)): ?>
        <div class="alert alert-danger"><?php echo $message_erreur; ?></div>
    <?php endif; ?>
    <?php if(!empty($message_succes)): ?>
        <div class="alert alert-success"><?php echo $message_succes; ?></div>
    <?php endif; ?>

    <form action="inscription.php" method="POST">
        
        <div class="form-group">
            <label>Nom complet</label>
            <div class="input-wrapper">
                <i class="fa-regular fa-user field-icon"></i>
                <input type="text" name="nom_complet" placeholder="Entrez votre nom complet" required value="<?php echo isset($_POST['nom_complet']) ? htmlspecialchars($_POST['nom_complet']) : ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Adresse e-mail</label>
            <div class="input-wrapper">
                <i class="fa-regular fa-envelope field-icon"></i>
                <input type="email" name="email" placeholder="Entrez votre adresse e-mail" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Numéro étudiant</label>
            <div class="input-wrapper">
                <i class="fa-regular fa-id-card field-icon"></i>
                <input type="text" name="numero_etudiant" placeholder="Entre votre numéro" required value="<?php echo isset($_POST['numero_etudiant']) ? htmlspecialchars($_POST['numero_etudiant']) : ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock field-icon"></i>
                <input type="password" id="password" name="password" placeholder="Créez votre mot de passe" required>
                <i class="fa-regular fa-eye toggle-password" onclick="togglePass('password')"></i>
            </div>
        </div>

        <div class="form-group">
            <label>Comfirmer le mot de passe</label> <div class="input-wrapper">
                <i class="fa-solid fa-lock field-icon"></i>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Comfirmer votre mot de passe" required>
                <i class="fa-regular fa-eye toggle-password" onclick="togglePass('confirm_password')"></i>
            </div>
        </div>

        <button type="submit" class="btn-submit">S'inscrire</button>
        <button type="reset" class="btn-cancel">Annuler</button>

    </form>

    <div class="footer-text">
        Vous avez déjà un compte ? <a href="connexion.php">Se connecter</a>
    </div>
</div>

<script>
    // Script pour afficher/masquer le mot de passe en cliquant sur l'œil
    function togglePass(idChamp) {
        var champ = document.getElementById(idChamp);
        if (champ.type === "password") {
            champ.type = "text";
        } else {
            champ.type = "password";
        }
    }
</script>

</body>
</html>