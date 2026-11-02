# JC Airlines – käyttö- ja koodidokumentaatio

Tämä dokumentti kertoo, miten JC Airlines -sivusto käynnistetään XAMPPissa, mitä sen tiedostot tekevät ja miten varaus etenee. Tarkoitus on, että osaat itse selittää ratkaisun ja näyttää sen opettajalle.

## 1. Projektin käynnistäminen XAMPPissa

### Ensimmäinen käynnistysasdasd

1. Asenna XAMPP ja avaa **XAMPP Control Panel**.
2. Kopioi `JC_Airlines_tehtävä`-kansio XAMPPin `htdocs`-kansioon. Tavallinen polku Windowsissa on `C:\xampp\htdocs\JC_Airlines_tehtävä`.
3. Käynnistä XAMPP Control Panelissa **Apache** ja **MySQL**. Molempien pitää näyttää käynnissä olevaa tilaa.
4. Avaa selaimessa `http://localhost/phpmyadmin`.
5. Valitse phpMyAdminista **Import / Tuo**, valitse projektin `database.sql` ja suorita tuonti. Tiedosto luo `lennot`-tietokannan, lentotaulun `lennot` ja tilaustaulun `tilaus`.
6. Avaa `http://localhost/JC_Airlines_tehtävä/`.

Jos tietokanta on jo tuotu, sitä ei tarvitse tuoda uudestaan joka käynnistyksellä. Kun jatkat työtä myöhemmin, avaa XAMPP ja käynnistä Apache sekä MySQL.

### Jos sivusto ei toimi

- **Sivu ei avaudu:** varmista, että Apache on käynnissä ja projektikansio on `htdocs`-kansiossa. Avaa sivu `http://localhost/...`-osoitteesta, älä avaamalla PHP-tiedostoa suoraan resurssienhallinnasta.
- **Tietokantavirhe tai tyhjä lentolista:** varmista, että MySQL on käynnissä ja `database.sql` on tuotu. phpMyAdminissa pitäisi näkyä tietokanta `lennot` ja sen alla taulut `lennot` sekä `tilaus`.
- **Yhteysasetukset:** `db.php` käyttää oletuksena osoitetta `127.0.0.1`, käyttäjää `root` ja tyhjää salasanaa. Tämä on tavallinen paikallisen XAMPPin asetus. Jos omassa XAMPPissa käyttäjällä on salasana, muuta asetuksia samassa tiedostossa.
- **Import sanoo tietokannan tai taulun olevan jo olemassa:** se voi olla normaalia. `CREATE TABLE IF NOT EXISTS` ei korvaa olemassa olevaa taulua eikä palauta sen paikkamääriä alkuperäisiksi.

## 2. Projektin tiedostot

| Tiedosto | Tehtävä |
|---|---|
| `index.php` | Etusivu, hakulomake, lentotulokset ja varausprosessi. |
| `db.php` | Tietokantayhteys sekä lentojen hakuun, tilausten tallennukseen ja sivujen tietoihin liittyvät PHP-funktiot. |
| `database.sql` | Luo tietokannan ja taulut sekä lisää annetut 35 lentoriviä. |
| `kalusto.php` | Hakee eri konetyypit `lennot`-taulusta ja esittelee ne. |
| `kohteet.php` | Hakee kaupungit, maat ja lentojen lukumäärät tietokannasta. |
| `aspa.php` | Asiakaspalvelusivu, yhteystiedot ja usein kysytyt kysymykset. |
| `style.css` | Sivuston värit, asettelu, komponentit ja mobiilinäytön tyylit. |
| `readme.md` | Lyhyt projektin kuvaus ja pikaohje käynnistämiseen. |

## 3. Miten PHP-sivut ja tietokanta toimivat yhdessä?

Selain pyytää esimerkiksi `index.php`-sivua. PHP suoritetaan palvelimella (XAMPPin Apache), joka hakee tarvittavat tiedot MySQL-tietokannasta. PHP muodostaa HTML-sivun ja lähettää sen selaimeen. Selain näyttää HTML:n ja soveltaa siihen `style.css`-tiedoston tyylejä.

PHP-tiedostossa HTML:n seassa oleva `<?php ... ?>` on palvelimella ajettavaa koodia. Esimerkiksi `<?= htmlspecialchars($kaupunki) ?>` tulostaa kaupungin HTML:ään turvallisesti. `htmlspecialchars` muuntaa erikoismerkit tekstiksi eikä selaimen tulkitsemaksi HTML-koodiksi.

`index.php` lataa yhteiset tietokantafunktiot rivillä `require __DIR__ . '/db.php';`. `__DIR__` tarkoittaa nykyisen tiedoston kansiota, joten tiedoston sijainti ei riipu siitä, mistä ohjelma käynnistetään.

## 4. Tietokannan rakenne

### `lennot`

Tämä on tehtävässä annettu taulu. Sen tärkeimmät sarakkeet ovat:

- `LentoID`: lennon yksilöivä tunniste eli pääavain.
- `Kohdemaa` ja `Kaupunki`: lennon maa ja kohdekaupunki.
- `Aika`: aamu, päivä tai ilta.
- `Kone`: konetyyppi.
- `LipunHinta`: yhden lentosuunnan hinta.
- `VapaatPaikat`: jäljellä olevien paikkojen määrä.

### `tilaus`

Tämä projektia varten lisätty taulu tallentaa tilauksen. `flight_id` viittaa siihen `lennot`-riviin, jonka asiakas valitsi. `departure_date`, `trip_type` ja `return_date` tallennetaan tilaukseen, koska alkuperäisessä lentotaulussa ei ole matkapäivää eikä meno/paluu-tietoa.

`CREATE TABLE IF NOT EXISTS` luo taulun vain, jos sitä ei vielä ole. `INSERT IGNORE` lisää annetut lentorivit, mutta ohittaa rivin, jos samalla pääavaimella oleva lento on jo olemassa. Siksi SQL-tiedoston uusi tuonti ei nollaa paikkamääriä jo käytössä olevassa tietokannassa.

## 5. Lennonhaun ja varauksen eteneminen

1. Hakulomake lähettää kohteen, lähtöajan, päivämäärän, matkatyypin ja lippumäärän `index.php`-sivulle.
2. `index.php` tarkistaa syötteitä, kuten päivämäärän muodon, matkatyypin ja lippumäärän rajat.
3. `jc_get_flights()` tekee tietokantahaun. Kohde ja ajankohta annetaan kyselylle parametreina; käyttäjän syötettä ei liitetä suoraan SQL-lauseeseen.
4. Asiakas valitsee lennon ja antaa tilaajan yhteystiedot. **Tarkista tilaus** näyttää yhteenvedon. Tässä vaiheessa tietokantaan ei vielä tehdä tilausta eikä paikkoja vähennetä.
5. Asiakas voi peruuttaa tai valita **Vahvista ja varaa**.
6. `jc_create_order()` aloittaa tietokantatapahtuman. Se tarkistaa vielä valitun lennon paikkatilanteen, vähentää paikat ja lisää tilauksen `tilaus`-tauluun.
7. Jos tallennus onnistuu, tapahtuma vahvistetaan (`commit`). Jos tapahtuu tietokantavirhe, muutokset perutaan (`rollBack`), jotta paikkojen vähennys ja tilaus eivät jää eri tilaan.
8. Vahvistuksen jälkeen sivu näyttää lentolipun esikatselun. Oikean sähköpostin lähetys on oletuksena pois päältä.

`PDO` on PHP:n tietokantakirjasto. Valmistellussa kyselyssä (`prepare` ja `execute`) arvot, kuten lennon tunniste ja lippumäärä, välitetään erikseen SQL-lauseesta. Tämä auttaa käsittelemään syötteet oikein ja välttämään SQL-injektioita.

## 6. Mobiilinäkymä ja ulkoasu

`style.css` käyttää CSS Grid- ja Flexbox-asetteluja. Sivun leveys, otsikot ja palstat mukautuvat näytön kokoon. `@media`-säännöt muuttavat erityisesti näkymää pienillä, enintään 620 pikselin levyisillä näytöillä. Hakulomake ja kortit asettuvat mobiilissa allekkain. Sivun HTML:n `viewport`-asetus kertoo selaimelle, että asettelu pitää mitoittaa laitteen näytön mukaan.

Esittelyä varten testaa sivu sekä normaalissa selainikkunassa että selaimen mobiilinäkymässä. Jos opettaja hyväksyy STEP 1:n layoutin, ota pyydetty kuvakaappaus hyväksytystä näkymästä.

## 7. Mitä ratkaisu ei mallinna täydellisesti?

Alkuperäisessä `lennot`-taulussa ei ole lähtökaupunkia eikä päivämäärää. Projekti käyttää lähtökaupunkina Tamperetta, jota ei ole taulun kohdekaupungeissa, ja asiakas valitsee päivämäärän varauksessa. Saman lentorivin hinta ja paikkamäärä ovat siis käytössä valittuna päivänä, kuten tehtävänannossa pyydetään.

Taulussa ei myöskään ole erillisiä paluulentorivejä. Meno-paluumatka näytetään vastakkaissuuntaisena paluuna, ja paikkamäärää vähennetään kahden osuuden verran. Tämä on yksinkertaistus tietokannan rajoituksen vuoksi, ei oikea lentoaikataulujen mallinnus.

Sähköposti käsitellään `password_hash()`-funktiolla. Se on yksisuuntainen tiiviste, ei salaus: alkuperäistä sähköpostia ei voi palauttaa tiivisteestä. Se sopii esimerkiksi salasanan tarkistamiseen, mutta ei tilanteeseen, jossa osoite pitää myöhemmin lukea tietokannasta sähköpostin lähettämistä varten. Tässä projektissa osoite on tallessa vain lomakkeen käsittelyn ajan, ja sähköpostin lähetys on pois päältä oletuksena. Tämä ero on hyvä mainita opettajalle.

## 8. Esittely opettajalle

Voit näyttää työn tässä järjestyksessä:

1. Näytä etusivu ja responsiivinen asettelu.
2. Hae esimerkiksi Oulun aamulento yhdelle matkustajalle ja näytä hinta sekä vapaat paikat.
3. Valitse lento, täytä testitiedot ja näytä tilauksen tarkistusvaihe. Valitse ensin **Peruuta tilaus** ja varmista phpMyAdminissa, ettei tilaus syntynyt eikä paikkamäärä muuttunut.
4. Tee uusi testitilaus ja vahvista se. Näytä sivun lentolippuesikatselu.
5. Avaa phpMyAdminin `lennot`-taulu ja näytä, että valitun lennon `VapaatPaikat` pieneni. Näytä myös tilaus `tilaus`-taulussa.
6. Näytä `Kohteet`, `Kalusto` ja `Asiakaspalvelu` -sivut.
7. Mainitse avoimesti, että tietokannan alkuperäinen rakenne ei sisällä lähtökaupunkia, päivämääriä eikä erillisiä paluulentoja, joten nämä osat on toteutettu yksinkertaistettuina.

Käytä esittelyssä vain keksittyjä testihenkilön tietoja.
