<?php
session_start();

// Pokud je uživatel přihlášený, pošle ho to rovnou na hlavní stránku
if (isset($_SESSION['prihlasen']) && $_SESSION['prihlasen'] === true) {
    header('Location: index.php');
    exit;
}

$chyba = "";

$hash_HonzaBarca = '$2y$12$Iz4lO19ZYSbqUvHmBZJ43e/WxbEJxS6h/AkaTnjWdGdL.6kxGXXxa';
$hash_ucitel = '$2y$12$ZM0h.QzvjEvmsGXbiJz4OusU7QVy8HWALNindXJPhsAXSOZ.Xwzfq';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zadane_heslo = isset($_POST['heslo']) ? $_POST['heslo'] : '';

    // Ověření hesel
    if (password_verify($zadane_heslo, $hash_HonzaBarca) || password_verify($zadane_heslo, $hash_ucitel)) {
        $_SESSION['prihlasen'] = true;
        header('Location: index.php');
        exit;
    } else {
        $chyba = "Nesprávné heslo!";
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vstup do Horrorové Databáze</title>
    <link rel="stylesheet" href="style.css?v=6">
    <!--- Favicony --->
    <link rel="icon" type="image/png" sizes="16x16" href="Favicons/Favicon16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Favicons/Favicon32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="Favicons/Favicon96.png"> 
    <style>
        main {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            width: 100%;
        }
        .error-msg {
            color: #ed1c24;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
            text-shadow: 0 0 5px rgba(237, 28, 36, 0.3);
        }
    </style>
</head>
<body class="addform">
    <header>
        <h1>Hororová Databáze</h1>
        <div class="line"></div>
    </header>

    <main>
        <div class="add-form">
            <h3 style="text-align: center; margin-top: 0;">Zadej heslo</h3>
            
            <?php if (!empty($chyba)): ?>
                <p class="error-msg"><?php echo $chyba; ?></p>
            <?php endif; ?>

            <form action="Login.php" method="POST">
                <input type="password" name="heslo" placeholder="Heslo..." required 
                    style="width: 100%; background: #0a0a0a; border: 1px solid #333; color: #e0e0e0; padding: 12px 15px;
                    margin-bottom: 20px; border-radius: 8px; outline: none; font-family: inherit;">
                
                <button type="submit">Vstoupit</button>
            </form>
        </div>
    </main>

    <footer>
        <p>© 2026 Jan Čáp</p>
    </footer>
</body>
</html>