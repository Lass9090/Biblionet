<?php
session_start();
require_once 'config.php';

$message_erreur = "";

// Traitement du formulaire lorsqu'il est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message_erreur = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message_erreur = "Format d'adresse e-mail invalide.";
    } else {
        // Recherche de l'utilisateur dans la base de données
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si l'utilisateur existe, on vérifie son mot de passe
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Initialisation des variables de session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom_complet'];
            $_SESSION['user_role'] = $user['role']; // 'etudiant' ou 'admin'

            // Redirection selon le rôle de l'utilisateur
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: espace_etudiant.php");
            }
            exit();
        } else {
            $message_erreur = "Adresse e-mail ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Bibliothèque Numérique</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #000000; /* Fond noir derrière la carte principale comme sur le modèle */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .logo-top {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-top i {
            color: #3b82f6;
            font-size: 60px;
        }

        .container {
            background-color: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 40px 30px;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            text-align: center;
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #000000;
            margin-bottom: 12px;
        }

        .subtitle {
            font-size: 13px;
            color: #333333;
            line-height: 1.5;
            margin-bottom: 30px;
            padding: 0 15px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 15px;
            font-weight: 700;
            color: #000000;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i.field-icon {
            position: absolute;
            left: 15px;
            color: #000000;
            font-size: 18px;
        }

        .input-wrapper input {
            width: 100%;
            padding: 16px 15px 16px 48px;
            background-color: #dcdcdc; /* Fond gris clair pour les inputs comme sur l'image */
            border: none;
            border-radius: 12px;
            font-size: 14px;
            color: #000000;
            outline: none;
        }

        .input-wrapper input::placeholder {
            color: #555555;
        }

        .input-wrapper .toggle-password {
            position: absolute;
            right: 15px;
            color: #000000;
            cursor: pointer;
            font-size: 18px;
        }

        .forgot-password {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 25px;
        }

        .forgot-password a {
            font-size: 13px;
            color: #2b17ff;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            background-color: #2b17ff;
            color: #ffffff;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 25px;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #1e0cdb;
        }

        .footer-text {
            font-size: 13px;
            color: #000000;
        }

        .footer-text a {
            color: #2b17ff;
            text-decoration: none;
            font-weight: 500;
            margin-left: 5px;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        /* Styles de l'alerte d'erreur */
        .alert {
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
            background-color: #fde8e8;
            color: #e53e3e;
            border: 1px solid #fca5a5;
        }
    </style>
</head>
<body>

<div class="logo-top">
    <i class="fa-solid fa-graduation-cap"></i>
</div>

<div class="container">
    <h1>Connexion</h1>
    <p class="subtitle">Bienvenue ! Connectez-vous pour accéder à votre espace étudiant.</p>

    <?php if(!empty($message_erreur)): ?>
        <div class="alert"><?php echo $message_erreur; ?></div>
    <?php endif; ?>

    <form action="connexion.php" method="POST">
        
        <div class="form-group">
            <label>Adresse e-mail</label>
            <div class="input-wrapper">
                <i class="fa-regular fa-envelope field-icon"></i>
                <input type="email" name="email" placeholder="Entrez votre adress e-mail" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock field-icon"></i>
                <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                <i class="fa-regular fa-eye toggle-password" onclick="togglePass()"></i>
            </div>
        </div>

        <div class="forgot-password">
            <a href="#">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="btn-submit">Se connecter</button>

    </form>

    <div class="footer-text">
        Vous n’avez pas de compte? <a href="inscription.php">Inscrivez-vous</a>
    </div>
</div>

<script>
    // Script pour basculer la visibilité du mot de passe
    function togglePass() {
        var champ = document.getElementById('password');
        if (champ.type === "password") {
            champ.type = "text";
        } else {
            champ.type = "password";
        }
    }
</script>

</body>
</html>