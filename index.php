<?php 
session_start();
if (!isset($_SESSION['prihlasen']) || $_SESSION['prihlasen'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<?php require 'db.php'; // Připojení k databázi
    // Stav pro zobrazení pouze viděných filmů
    $filtr_stav = 1; // 1 = Viděli jsme, 0 = Chceme vidět
    if (isset($_GET['stav'])) {
        $filtr_stav = (int)$_GET['stav']; // Int zajišťuje, že to bude vždy číslo
    }

    // Získání hledaného výrazu z URL, pokud je nastaven
    $search_text = "";
    if (!empty($_GET['search'])) {
        $search_text = $_GET['search'];
    }

    // Získání všech filmů z databáze
    $sql = "SELECT
                filmy.*,
                h_honza.body AS body_honza,
                h_honza.komentar AS komentar_honza,
                h_barca.body AS body_barca,
                h_barca.komentar AS komentar_barca
            FROM filmy
            LEFT JOIN hodnoceni AS h_honza
                ON filmy.id_filmy = h_honza.filmy_id
                AND h_honza.autor = 'Honza'
            LEFT JOIN hodnoceni AS h_barca
                ON filmy.id_filmy = h_barca.filmy_id
                AND h_barca.autor = 'Barča'
            WHERE filmy.stav = :stav 
            ORDER BY filmy.nazev ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'stav' => $filtr_stav,
    ]);

    $filmy = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hororová Databáze</title>
    <link rel="stylesheet" href="style.css?v=6">
    <!--- Favicony --->
    <link rel="icon" type="image/png" sizes="16x16" href="Favicons/Favicon16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Favicons/Favicon32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="Favicons/Favicon96.png"> 
</head>
<body>
    <header>
        <h1>Hororová Databáze</h1>
        <div class="line"></div>
        <nav class="tabs">
            <!-- Class filtruje stavy, podle toho, který je aktivní, zobrazí se -->
            <a href="index.php?stav=1" class="<?php echo ($filtr_stav == 1) ? 'active' : ''; ?>">
                [ Viděli jsme ]
            </a>
            <a href="index.php?stav=0" class="<?php echo ($filtr_stav == 0) ? 'active' : ''; ?>">
                [ Chceme vidět ]
            </a>
            <a href="AddForm.php" class="btn_add">[ Přidat nový horror ]</a>
            <a href="logout.php">[ Odhlásit se ]</a>
        </nav>
        <div class="line"></div>
    </header>

    <main>   
        <div class="search-bar">
            <input id="search-input" type="text" placeholder="Hledat horor...">
        </div>
        <section class="film-grid">
            <?php if (!empty($filmy)): ?> <!-- Pokud v poli něco je, PHP vypíše filmy -->
                <?php foreach ($filmy as $film): ?>
                    <div class="film-card">
                        <div class="poster-wrapper">
                            <img src="" alt="Plakát" class="film-poster" data-title="<?php echo htmlspecialchars($film['nazev']); ?>">
                            <span class="year"><?php echo $film ['rok']; ?></span>
                        </div>
                        
                        <div class="film-info">
                            <div class="film-header">
                                <h3><?php echo htmlspecialchars($film['nazev']); ?></h3>
                                <div class="edit-buttons">
                                    <a href="EditForm.php?id=<?php echo $film['id_filmy']; ?>" class="btn-edit">
                                        <img src="Pictures/EditIcon50.png" alt="Upravit" width="24">
                                    </a>
                                    <a href="Delete.php?id=<?php echo $film['id_filmy']; ?>" onclick="return confirm('Opravdu chcete tento film trvale smazat? Zmizí i vaše hodnocení.')" class="delete-btn">
                                        <img src="Pictures/DeleteIcon50.png" alt="Smazat" width="26">
                                    </a>
                                </div>
                            </div>
                            <details class="description-container">
                                <summary> Zobrazit popis...</summary>
                                <div class="description-content">
                                    <p class="description"><?php echo $film ['popis']; ?></p>
                                </div>
                            </details>
                            <div class="ratings">
                                <!-- Body Honza -->
                                <div class="rating-bubble honza">
                                    <span class="author">Honza</span>
                                    <span class="score">
                                        <?php echo isset($film['body_honza']) ? $film['body_honza'] : '-'; ?>/10
                                    </span>
                                </div>
                                <div class="rating-bubble barca">
                                    <!-- Body Barča -->
                                    <span class="author">Barča</span>
                                    <span class="score">
                                        <?php echo isset($film['body_barca']) ? $film['body_barca'] : '-'; ?>/10
                                    </span>
                                </div>
                                <details class="reviews-container">
                                    <summary> Zobrazit naše komentáře...</summary>
                                    <div class="text-reviews">
                                        <!-- Komentář Honza -->
                                        <?php if (!empty($film['komentar_honza'])): ?> <!-- !empty kontroluje, zda je komentář prázdný, a pokud není, zobrazí ho -->
                                            <p class="review"><strong>Honza:</strong> "<?php echo $film['komentar_honza']; ?>"</p>
                                        <?php endif; ?>
                                        <!-- Komentář Barča -->
                                        <?php if (!empty($film['komentar_barca'])): ?>
                                            <p class="review"><strong>Barča:</strong> "<?php echo $film['komentar_barca']; ?>"</p>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Pokud v poli není nic, PHP vypíše tenhle text -->
                <div class="empty-message">
                    <p>Nic na seznamu. Chce to najít nový horor!</p>
                    <img src="Pictures/Scream.png" alt="Scream" style="max-width: 600px;">
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>© 2026 Jan Čáp | GitHub <img src="Pictures/icons8-github-48.png" alt="GitHub" width="15"></p>
    </footer>
    <script>
        // Automatické vyhledávání
        const searchInput = document.getElementById('search-input');
        const filmCard = document.querySelectorAll('.film-card');

        searchInput.addEventListener('input', function (filmSearch) {
            let filterText = searchInput.value.toLowerCase();

            filmCard.forEach(function(card) {
                let movieName = card.querySelector('h3').textContent.toLowerCase();
                if (movieName.includes(filterText)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            })
        });

        // API pro načítání plakátů
        const apiKey = 'dfd51d2f';
        const posters = document.querySelectorAll('.film-poster');

        posters.forEach(function(img) {
            let title = img.getAttribute('data-title');
            let url = 'https://www.omdbapi.com/?t=' + encodeURIComponent(title) + '&apikey=' + apiKey;

            fetch(url)
                .then(function(response) {
                    return response.json(); // Rozbalení balíčku z API
                })
                .then(function(data) {
                    if (data.Response === 'True' && data.Poster !== "N/A") {
                        img.src = data.Poster;

                        // Záchranná brzda: OMDb posílá mrtvý odkaz (Například u filmu The Borderlands)
                        img.onerror = function() {
                            img.src = 'Pictures/NotFound.jpg'
                        };

                    } else {
                        // Film nenalezen (Pravděpodobně české názvy)
                        img.src = 'Pictures/NotFound.jpg'
                    }
                })
                .catch(function(error) {
                    console.error('Chyba při stahování plakátu:', error);
                });
        });
    </script>
</body>
</html>