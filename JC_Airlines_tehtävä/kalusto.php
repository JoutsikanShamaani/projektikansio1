<?php require __DIR__ . '/db.php'; ?>
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
            <div class="container card-grid">
                <article class="card">
                    <div class="icon">✈️</div>
                    <h3>Airbus A320neo</h3>
                    <p>Taloudellinen, hiljainen ja tehokas matkustajakone, joka sopii lyhyille ja keskipitkiin reittiin.</p>
                </article>
                <article class="card">
                    <div class="icon">🌍</div>
                    <h3>Airbus A330</h3>
                    <p>Suurempi matkustajakapasiteetti pitkille reiteille ja mukavat istuimet sekä laaja käsipankkihylly.</p>
                </article>
                <article class="card">
                    <div class="icon">💺</div>
                    <h3>Embraer E190</h3>
                    <p>Pienempi, ketterä kone sisäisille lennoille, joka varmistaa nopean ja yhtenäisen palvelun.</p>
                </article>
            </div>
        </section>

        <section class="section" style="background: rgba(103,209,255,0.04);">
            <div class="container">
                <div class="section-header">
                    <h2>Palvelut koneessa</h2>
                    <p>Kaikki koneet ovat suunniteltu matkustajakokemuksen parantamiseksi, turvallisuuden ja mukavuuden vaatimuksesta.</p>
                </div>
                <div class="feature-strip">
                    <div class="stat"><strong>180</strong> matkustajaa</div>
                    <div class="stat"><strong>Wi-Fi</strong> saatavilla</div>
                    <div class="stat"><strong>USB</strong> lataus</div>
                    <div class="stat"><strong>Premium</strong> palvelu</div>
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
