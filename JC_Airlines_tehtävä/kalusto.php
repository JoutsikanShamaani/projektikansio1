<?php
require __DIR__ . '/db.php';
$aircraftList = jc_get_aircraft();
$aircraftDescriptions = [
    'Q400' => 'Potkuriturbiinikone, joka sopii hyvin lyhyille ja alueellisille reiteille.',
    'A220-100' => 'Kompakti suihkukone, jossa yhdistyvät matkustusmukavuus ja tehokkuus.',
    'A220-300' => 'A220-perheen suurempi versio tarjoaa lisää tilaa keskipitkille reiteille.'
];
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Airlines | Kalusto</title>
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
                <a href="kalusto.php" class="active">Kalusto</a>
                <a href="kohteet.php">Kohteet</a>
                <a href="aspa.php">Asiakaspalvelu</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="mini-hero">
            <div class="container">
                <h1>Moderni kalusto matkustajaystävällisyyttä ajatellen</h1>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-header">
                    <h2>Tietokannan lentokoneet</h2>
                    <p>Kalustoluettelo perustuu lennot-taulun Kone-sarakkeen tietoihin.</p>
                </div>
                <?php if (!$aircraftList): ?>
                    <div class="info-box">
                        <p>Kalustoa ei voitu ladata. Tarkista MySQL-yhteys ja tietokannan sisältö.</p>
                    </div>
                <?php else: ?>
                    <div class="card-grid">
                        <?php foreach ($aircraftList as $aircraft): ?>
                            <article class="card">
                                <div class="icon" aria-hidden="true">✈</div>
                                <span class="eyebrow">JC Airlinesin kalusto</span>
                                <h3><?= htmlspecialchars($aircraft) ?></h3>
                                <p><?= htmlspecialchars($aircraftDescriptions[$aircraft] ?? 'Matkustajakone JC Airlinesin reittiverkostossa.') ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" style="background: rgba(103,209,255,0.04);">
            <div class="container">
                <div class="section-header">
                    <h2>Palvelut koneessa</h2>
                    <p>Reitin lentokonetyyppi näkyy lennonhaun tuloksissa suoraan tietokannan tiedoista.</p>
                </div>
                <div class="feature-strip">
                    <div class="stat"><strong><?= count($aircraftList) ?></strong>konetyyppiä</div>
                    <div class="stat"><strong>Q400</strong>alueelliset reitit</div>
                    <div class="stat"><strong>A220</strong>matkustusmukavuutta</div>
                    <div class="stat"><strong>35</strong>lentoriviä</div>
                </div>
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
                <p>Yhteensopiva matkailu ja ammattitaitoinen lentopalvelu.</p>
            </div>
            <div>
                <h4>Yhteystiedot</h4>
                <ul>
                    <li>asiakaspalvelu@jairlines.fi</li>
                    <li>+358 40 300 4500</li>
                </ul>
            </div>
            <div>
                <h4>Palvelut</h4>
                <ul>
                    <li>Yksityislennoista</li>
                    <li>Matkatavarat</li>
                    <li>Lentoaikataulut</li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>
