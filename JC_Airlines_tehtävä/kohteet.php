<?php
require __DIR__ . '/db.php';
$destinations = jc_get_destinations();
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Airlines | Kohteet</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container navbar">
            <a href="index.php" class="brand">
                <span class="brand-mark">JC</span>
                <span>JC Airlines</span>
            </a>
            <nav class="nav-links" aria-label="Päävalikko">
                <a href="index.php">Etusivu</a>
                <a href="kalusto.php">Kalusto</a>
                <a href="kohteet.php" class="active">Kohteet</a>
                <a href="aspa.php">Asiakaspalvelu</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="mini-hero">
            <div class="container">
                <h1>Suomen ja Euroopan suosituimmat kohteet</h1>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-header">
                    <h2>Kohteet lennot-tietokannasta</h2>
                    <p>Valitse kohde ja tutustu saatavilla oleviin lentovaihtoehtoihin.</p>
                </div>
                <?php if (!$destinations): ?>
                    <div class="info-box">
                        <p>Kohteita ei voitu ladata. Tarkista, että MySQL on käynnissä ja lennot-tietokanta on tuotu phpMyAdminiin.</p>
                    </div>
                <?php else: ?>
                    <div class="route-list">
                        <?php foreach ($destinations as $destination): ?>
                            <?php
                            $cityDescriptions = [
                                'Helsinki' => 'Suomen merellinen pääkaupunki yhdistää designin, kulttuurin ja kaupunkiloman.',
                                'Oulu' => 'Pohjoisen teknologiakaupunki tarjoaa tapahtumia ja merellistä tunnelmaa.',
                                'Rovaniemi' => 'Lapin portti tunnetaan arktisesta luonnosta, revontulista ja joulun tunnelmasta.',
                                'Oslo' => 'Norjan pääkaupungissa kohtaavat vuonot, museot ja moderni kaupunkielämä.',
                                'Bergen' => 'Värikäs satamakaupunki avaa näkymät Norjan vuonoihin ja vuoristoon.',
                                'Arlanda' => 'Tukholman lentoaseman kautta saavut helposti Ruotsin pääkaupunkiseudulle.',
                                'Göteborg' => 'Ruotsin länsirannikon kaupunki tunnetaan rennosta tunnelmasta ja satamasta.',
                                'Keflaki' => 'Islannin Keflavíkin alueelta on helppo jatkaa Reykjavíkiin ja luontokohteisiin.',
                                'Malmö' => 'Etelä-Ruotsin Malmö tarjoaa rantoja, ruokakulttuuria ja yhteydet Kööpenhaminaan.',
                                'Trondheim' => 'Historiallinen Trondheim yhdistää norjalaisen kulttuurin ja luonnon.',
                                'Kööpenhamina' => 'Tanskan pääkaupunki houkuttelee pyöräilyreiteillä, ruoalla ja designilla.',
                                'Billund' => 'Billund sopii erityisesti perheille ja Legolandissa vieraileville.'
                            ];
                            $description = $cityDescriptions[$destination['city']] ?? 'Tutustu kohteeseen ja suunnittele seuraava matkasi.';
                            $searchUrl = 'index.php?' . http_build_query(['destination_city' => $destination['city']]) . '#haku';
                            ?>
                            <article class="route-card">
                                <span class="eyebrow"><?= htmlspecialchars($destination['country']) ?></span>
                                <h3><?= htmlspecialchars($destination['city']) ?></h3>
                                <p><?= htmlspecialchars($description) ?></p>
                                <p class="route-count"><?= (int)$destination['flight_count'] ?> lentovaihtoehtoa tietokannassa</p>
                                <a class="text-link" href="<?= htmlspecialchars($searchUrl) ?>">Hae lentoja <span aria-hidden="true">→</span></a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" style="background: rgba(103,209,255,0.04);">
            <div class="container">
                <div class="section-header">
                    <h2>Lisäpalvelut</h2>
                    <p>Lisää matkakokemukseen enemmän mukavuutta ja joustavuutta.</p>
                </div>
                <ul class="service-list">
                    <li>Matkatavarapalvelu ja ennakkovaraukset sekä kansainväliset lisäpalvelut.</li>
                    <li>Priority boarding ja lisäistuimet sekä matkustamolipun valinnat.</li>
                    <li>Ravintolapalvelut, baari- ja kahvitarjoilu lähtiessä sekä toimitusulkomaan.</li>
                    <li>Perheystävälliset palvelut ja matkustusavustukset.</li>
                </ul>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <div class="brand" style="margin-bottom: 12px;">
                    <span class="brand-mark">JC</span>
                    <span>JC Airlines</span>
                </div>
                <p>Maisemien, matkustajien ja palvelun välittäjä.</p>
            </div>
            <div>
                <h4>Yhteystiedot</h4>
                <ul>
                    <li>+358 40 300 4500</li>
                    <li>info@jairlines.fi</li>
                </ul>
            </div>
            <div>
                <h4>Kohteet</h4>
                <ul>
                    <li>Suomi</li>
                    <li>Eurooppa</li>
                    <li>Lapin alueet</li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>
