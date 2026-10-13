# devops kaavio

```mermaid
flowchart LR
    subgraph DEV["Kehitys (Development)"]
        direction TB
        P[1. Plan<br/>Suunnittelu]
        C[2. Code<br/>Koodaus]
        B[3. Build<br/>Kokoaminen]
        T[4. Test<br/>Testaus]
    end

    subgraph OPS["Ylläpito (Operations)"]
        direction TB
        R[5. Release<br/>Julkaisu]
        D[6. Deploy<br/>Käyttöönotto]
        O[7. Operate<br/>Ylläpito]
        M[8. Monitor<br/>Seuranta]
    end

    P --> C --> B --> T --> R --> D --> O --> M
    M -.->|Palaute| P


    ## devops vaiheet

    Suunnittelu; tiimi pohtii ja päättää yhdessä mitä seuraavaksi rakennetaan. Ylös kirjataan vaatimukset sekä prioriteetit
    Koodaus; kehittäjät kirjoittavat koodia versiohallintaan githubissa
    Build; Koodi paketoidaan ajettavaksi
    Testaus; automaattiset testit (yksikkö, integraatio)
    Julkaisu; Testattu versio on julkaisukelpoinen joten sitä aletaan viemään tuotantoympäristöä kohti
    Käyttöönotto; Versio viedään tuotantoympäristöön
    Ylläpito; Sovellus toimii tuotannossa, työkalut seuraavat kuormitusta ja virheitä reaaliajassa
    Seuranta; Kerättyä dataa analysoidaan ja ongelmat sekä käyttäjäpalaute otetaan huomioon. Tämän jälkeen suunnitteluvaihe alkaa   uudelleen