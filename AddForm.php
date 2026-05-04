<?php require 'db.php'; 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Získání dat z formuláře
    $nazev = $_POST['nazev'];
    $rok = (int)$_POST['rok'];
    $popis = $_POST['popis'];
    $stav = (int)$_POST['stav'];

    // Vložení do databáze
    $sql = "INSERT INTO filmy (nazev, rok, popis, stav) VALUES (:nazev, :rok, :popis, :stav)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'nazev' => $nazev,
        'rok' => $rok,
        'popis' => $popis,
        'stav' => $stav
    ]);
        
    // Získává ID právě vloženého filmu pro následné vložení hodnocení
    $filmy_id = $conn->lastInsertId();

    // Dotaz pro hodnocení, připravený pro pozdější použití
    $sql_hodnoceni = "INSERT INTO hodnoceni (filmy_id, autor, body, komentar) VALUES (:filmy_id, :autor, :body, :komentar)";
    $stmt_h = $conn->prepare($sql_hodnoceni);

    // Hodnocení Honza (vkládá se pouze, pokud se vyplní body a komentář)
    if (!empty($_POST['body_honza']) || !empty($_POST['komentar_honza'])) {
        $stmt_h->execute([
            'filmy_id' => $filmy_id,
            'autor' => 'Honza',
            'body' => (int)$_POST['body_honza'],
            'komentar' => $_POST['komentar_honza']
        ]);
    }

    // Hodnicení Barča 
    if (!empty($_POST['body_barca']) || !empty($_POST['komentar_barca'])) {
        $stmt_h->execute([
            'filmy_id' => $filmy_id,
            'autor' => 'Barča',
            'body' => (int)$_POST['body_barca'],
            'komentar' => $_POST['komentar_barca']
        ]);
    }

    // Přesměrování zpět na hlavní stránku
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přidat nový film</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="addform">
        <h1>Pridat nový film</h1>
        <a href="index.php">Zpět na hlavní stránku</a>
    </header>

    <main>
        <form action="AddForm.php" method="POST" class="add-form">
            <input type="text" name="nazev" placeholder="Název filmu" required>
            <input type="number" name="rok" placeholder="Rok vydání" required>
            <textarea name="popis" placeholder="Popis filmu" required></textarea>
            <label>
                <input type="radio" name="stav" value="1" checked>
                Viděli jsme
            </label>
            <label>
                <input type="radio" name="stav" value="0">
                Chceme vidět
            </label>

            <hr>

            <h3>Hodnocení Honza</h3>
            <input type="number" name="body_honza" placeholder="Hodnocení (1-10)" min="1" max="10">
            <textarea name="komentar_honza" placeholder="Komentář"></textarea>

            <h3>Hodnocení Barča</h3>
            <input type="number" name="body_barca" placeholder="Hodnocení (1-10)" min="1" max="10">
            <textarea name="komentar_barca" placeholder="Komentář"></textarea>

            <button type="submit">Přidat film</button>
        </form>
    </main>
</body>
</html>