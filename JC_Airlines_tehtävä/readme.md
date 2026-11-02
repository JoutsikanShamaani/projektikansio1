# JC Airlines

Responsiivinen lentoyhtiösivusto koulutehtävää varten. Sivusto käyttää tehtävässä annettua `lennot`-taulua ja tallentaa varaukset omaan `tilaus`-tauluun.

Katso tarkempi käyttö-, XAMPP- ja koodiselitys tiedostosta [DOKUMENTAATIO.md](./DOKUMENTAATIO.md).

## Käynnistys XAMPPissa

1. Kopioi `JC_Airlines_tehtävä` XAMPPin `htdocs`-kansioon.
2. Käynnistä **Apache** ja **MySQL** XAMPP Control Panelista.
3. Avaa phpMyAdmin osoitteessa `http://localhost/phpmyadmin`.
4. Tuo tämän kansion `database.sql`-tiedosto. Se luo `lennot`-tietokannan, tehtävän mukaisen lentotaulun ja tilaustaulun.
5. Avaa sivusto osoitteessa `http://localhost/JC_Airlines_tehtävä/`.

Tietokantayhteyden asetukset ovat tiedostossa `db.php`. Oletuksena käytössä on XAMPPin `root`-käyttäjä ilman salasanaa.

## Varausprosessi

Etusivulta haetaan kohteen, lähtöajan, päivän, matkatyypin ja lippumäärän perusteella vapaita lentoja. Lento valitaan varaukseen, asiakkaan tiedot täytetään ja tilaus tarkistetaan ennen vahvistamista. Vahvistettu tilaus tallentuu `tilaus`-tauluun ja paikkamäärä vähenee `lennot`-taulusta tietokantatapahtuman sisällä. Meno-paluumatka varaa paikat molemmille osuuksille. Tilauksen sähköpostiosoite tallennetaan `password_hash()`-arvona.

Annetussa lentotaulussa ei ole lähtökaupunkia eikä päivämäärää. Sivusto käyttää lähtökaupunkina Tamperetta, jota ei löydy taulun kohdekaupungeista, ja päivämäärä valitaan varauksen yhteydessä. Lentotaulun paikkamäärä ja hinta ovat käytössä jokaiselle valitulle päivälle kuten tehtävänannossa kuvataan. Koska tietokannassa ei ole erillisiä paluulentorivejä, meno-paluun paluu käyttää valittua reittiä vastakkaiseen suuntaan ja samaa lentotietuetta.

Kouluprojekti näyttää vahvistetun lentolipun esikatselun sivustolla. Sähköpostin lähetys on oletuksena pois päältä; sen voi ottaa käyttöön asettamalla PHP:n ympäristömuuttujan `JC_SEND_EMAIL=1` ja määrittämällä sähköpostin XAMPPissa.

Etusivun taustakuva on Unsplash-palvelusta ja sitä käytetään Unsplash-lisenssin mukaisesti.
