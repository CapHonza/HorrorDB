<?php 
session_start();
if (!isset($_SESSION['prihlasen']) || $_SESSION['prihlasen'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<?php 
require 'db.php';

// Kontrola, jestli přišlo ID filmu, který chci smazat
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Nejdříve se maže hodnocení
    $sql_hodnoceni = "DELETE FROM hodnoceni WHERE filmy_id = :id";
    $stmt_h = $conn->prepare($sql_hodnoceni);
    $stmt_h->execute(['id' => $id]);

    // Potom samotný gilm
    $sql_film = "DELETE FROM filmy WHERE id_filmy = :id";
    $stmt_f = $conn->prepare($sql_film);
    $stmt_f->execute(['id' => $id]);
}

// Přesměrování na hlavní stránku
header('Location: index.php');
exit;
?>