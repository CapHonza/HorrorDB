<?php 
session_start();
if (!isset($_SESSION['prihlasen']) || $_SESSION['prihlasen'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<?php 
require 'db.php'; // Připojení k databázi

// Kontrola, jestli uživatel přišel formulářem (POSTem)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vytažení dat z formuláře do proměnných
    $id = (int)$_POST['id'];
    $nazev = $_POST['nazev'];
    $rok = (int)$_POST['rok'];
    $popis = $_POST['popis'];
    $stav = (int)$_POST['stav'];

    // SQL UPDATE
    // SET mi říká, co měním, WHERE zase který film měním
    $sql_update_filmy = "UPDATE filmy 
        SET nazev = :nazev, rok = :rok, popis = :popis, stav = :stav 
        WHERE id_filmy = :id";

    // Poslání do databáze
    $stmt = $conn->prepare($sql_update_filmy);
    $stmt->execute([
        'nazev' => $nazev,
        'rok' => $rok,
        'popis' => $popis,
        'stav' => $stav,
        'id' => $id,
    ]);

    if ($stav === 1) {
        $body_honza = $_POST['body_honza'] !== '' ? (float)$_POST['body_honza'] : null;
        $komentar_honza = $_POST['komentar_honza'];

        // Otázka na databázi, jestli záznam už existuje
        $stmt_check = $conn->prepare("
            SELECT id_hodnoceni
            FROM hodnoceni
            WHERE filmy_id = :id AND autor = 'Honza'
        ");
        $stmt_check->execute(['id' => $id]);
        $zaznam_existuje = $stmt_check->fetch();

        // Honza
        if ($zaznam_existuje) {
            // Pokud už je záznam v databázi uložen, tak se akorát aktualizuje (UPDATE)
            $stmt_u = $conn->prepare("
                UPDATE hodnoceni
                SET body = :body, komentar = :komentar
                WHERE filmy_id = :id AND autor = 'Honza'
            ");
            $stmt_u->execute([
                'body' => $body_honza,
                'komentar' => $komentar_honza,
                'id' => $id
            ]);
        } else {
            // Pokud tam záznam není, tak se vloží (INSERT)
            $stmt_i = $conn->prepare("
                INSERT INTO hodnoceni (filmy_id, autor, body, komentar)
                VALUES (:id, 'Honza', :body, :komentar)
            ");
            $stmt_i->execute([
                'id' => $id,
                'body' => $body_honza,
                'komentar' => $komentar_honza
            ]);
        }

        $body_barca = $_POST['body_barca'] !== '' ? (float)$_POST['body_barca'] : null;
        $komentar_barca = $_POST['komentar_barca'];

        $stmt_check = $conn->prepare("
            SELECT id_hodnoceni
            FROM hodnoceni
            WHERE filmy_id = :id AND autor = 'Barča'
        ");
        $stmt_check->execute(['id' => $id]);
        $zaznam_existuje = $stmt_check->fetch();

        // Barča
        if ($zaznam_existuje) {
            $stmt_u = $conn->prepare("
                UPDATE hodnoceni
                SET body = :body, komentar = :komentar
                WHERE filmy_id = :id AND autor = 'Barča'
            ");
            $stmt_u->execute([
                'body' => $body_barca,
                'komentar' => $komentar_barca,
                'id' => $id
            ]);
        } else {
            $stmt_i = $conn->prepare("
                INSERT INTO hodnoceni (filmy_id, autor, body, komentar)
                VALUES (:id, 'Barča', :body, :komentar)
            ");
            $stmt_i->execute([
                'id' => $id,
                'body' => $body_barca,
                'komentar' => $komentar_barca
            ]);
        }
    }
    // Přesměrování zpět na hlavní stránku
    header("Location: index.php?stav=" . $stav);
    exit;
}

// Načtení dat pro formulář
// Kontrola, jestli v URL adrese existuje "?id="
if (!isset($_GET['id'])) {
    header('Location: index.php'); // Bez id neví, co upravovat
    exit;
}

// Uložení id
$id = (int)$_GET['id'];

// Vytažení základních informací o filmu
$sql_film = "SELECT * FROM filmy WHERE id_filmy = :id";
$stmt_film = $conn->prepare($sql_film);
$stmt_film->execute(['id' => $id]);
$film = $stmt_film->fetch();

// Když někdo zkusí do URL napsat id filmu, které neexistuje
if (!$film) {
    header('Location: index.php');
    exit;
}

// Vytažení všech hodnocení pro daný film
$sql_hodnoceni = "SELECT * FROM hodnoceni WHERE filmy_id = :id";
$stmt_h = $conn->prepare($sql_hodnoceni);
$stmt_h->execute(['id' => $id]);
$hodnoceni_vse = $stmt_h->fetchAll();

// Příprava proměnné
$hodnoceni_honza = null;
$hodnoceni_barca = null;

// Procházení výsledků a rozdělení pro napojení do formuláře
foreach ($hodnoceni_vse as $h) {
    if ($h['autor'] === 'Honza')
        $hodnoceni_honza = $h;
    elseif ($h['autor'] === 'Barča') {
        $hodnoceni_barca = $h;
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravit film: <?php echo htmlspecialchars($film['nazev']); ?></title>
    <link rel="stylesheet" href="style.css">
    <!--- Favicony --->
    <link rel="icon" type="image/png" sizes="16x16" href="Favicons/Favicon16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Favicons/Favicon32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="Favicons/Favicon96.png"> 
</head>
<body>
    <header class="addform">
        <h1>Upravit film</h1>
        <a href="index.php?stav=<?php echo $film['stav']; ?>">Zpět na hlavní stránku</a>
    </header>

    <main>
        <form action="EditForm.php" method="POST" class="add-form">           
            <input type="hidden" name="id" value="<?php echo $film['id_filmy']; ?>">
            <input type="text" name="nazev" placeholder="Název filmu" value="<?php echo htmlspecialchars($film['nazev']); ?>" required>
            <input type="number" name="rok" placeholder="Rok vydání" value="<?php echo $film['rok']; ?>" required>
            <textarea name="popis" placeholder="Popis filmu" required><?php echo htmlspecialchars($film['popis']); ?></textarea>
            <label>
                <input type="radio" name="stav" value="1" <?php echo ($film['stav'] == 1) ? 'checked' : ''; ?>>
                Viděli jsme
            </label>
            <label>
                <input type="radio" name="stav" value="0" <?php echo ($film['stav'] == 0) ? 'checked' : ''; ?>>
                Chceme vidět
            </label>
            <hr>
            <div id="rating-box">
                <h3>Hodnocení Honza</h3>
                <input type="number" name="body_honza" placeholder="Hodnocení (1-10)" min="1" max="10" step="0.1" value="<?php echo isset($hodnoceni_honza) ? $hodnoceni_honza['body'] : ''; ?>">
                <textarea name="komentar_honza" placeholder="Komentář"><?php echo isset($hodnoceni_honza) ? htmlspecialchars($hodnoceni_honza['komentar']) : ''; ?></textarea>
                
                <h3>Hodnocení Barča</h3>
                <input type="number" name="body_barca" placeholder="Hodnocení (1-10)" min="1" max="10" step="0.1" value="<?php echo isset($hodnoceni_barca) ? $hodnoceni_barca['body'] : ''; ?>">
                <textarea name="komentar_barca" placeholder="Komentář"><?php echo isset($hodnoceni_barca) ? htmlspecialchars($hodnoceni_barca['komentar']) : ''; ?></textarea>
            </div>
            <button type="submit">Uložit změny</button>
        </form>
    </main> 

    <script>
        // Inteligentní formulář (schovává/ukazuje hodnocení podle toho, kam chcem film zařadit)
        const ratingBox = document.getElementById('rating-box');
        const radioButtons = document.querySelectorAll('input[name="stav"]');

        function ratingToggle () {
            let chosenValue = document.querySelector('input[name="stav"]:checked').value;
            if (chosenValue === "1") { // Z HTML jde vždy string, proto porovnávám string ("1") a ne INT (1)
                ratingBox.style.display = 'block'; // Ukáže box
            } else {
                ratingBox.style.display = 'none'; // Schová box
            }
        }

        radioButtons.forEach(function(radio) {
            radio.addEventListener('change', ratingToggle);
        });
        ratingToggle(); 
    </script>
</body>
</html>