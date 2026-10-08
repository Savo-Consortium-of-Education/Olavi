# Pienyrityksen Taloushallinto

Yksinkertainen web-pohjainen taloushallintojärjestelmä pienyrityksille. Ohjelman avulla voit hallita menoja, tuloja, matkalaskuja, puhelin- ja tietoliikennekuluja, sekä seurata yrityksen kannattavuutta ja käsitellä vero- ja ALV-ilmoituksia.

## Ominaisuudet

- **Tapahtumien syöttö**: Lisää tuloja ja menoja helpolla lomakkeella
- **Kategorisointi**: Jaa menot kategorioihin (yleinen meno, matkalasku, puhelin/tietoliikenne)
- **ALV-hallinta**: Automaattinen ALV-laskenta ja seuranta
- **Raportointi**: 
  - Kannattavuusraportti (kokonaistulot, menot, voittomarginaali)
  - Kvartaaliraportit (Q1-Q4)
- **Veroilmoitukset**: 
  - ALV-ilmoitus (maksettava ALV, vähennettävä ALV, saldo)
  - Veroilmoitus (verotettava tulo)
- **Siirtotiedostot**: CSV-vienti raportteille verottajalle
- **Kirjautuminen ja roolit**: Sovellus vaatii kirjautumisen. Käyttäjät ovat omistajia (hallitsevat käyttäjiä) tai kirjanpitäjiä

## Teknologia

- **Backend**: PHP 8.2 (Apache)
- **Tietokanta**: MySQL 8.0
- **Frontend**: HTML5, oma CSS (`src/assets/style.css`), ei ulkoisia kirjastoja
- **Konttienhallinta**: Docker Compose

## Asennus ja käynnistys

### Vaatimukset
- Docker
- Docker Compose

### Käynnistäminen

1. Kloonaa tai lataa projekti
2. Siirry projektin hakemistoon
3. Käynnistä kontit:
```bash
docker-compose up -d
```

4. Odota, kunnes tietokanta on valmis (n. 10-15 sekuntia)
5. Luo ensimmäinen omistaja. Sovelluksessa ei ole oletustunnuksia, joten ilman tätä vaihetta sisään ei pääse (komento kysyy
   salasanan kahdesti; lisätietoja kohdassa [Kirjautuminen ja käyttäjät](#kirjautuminen-ja-käyttäjät)):
```bash
docker compose exec web php cli/manage_users.php create --username=olavi --name="Olavi Esimerkki" --role=owner
```

6. Avaa selaimessa `http://localhost:8080` ja kirjaudu sisään

Jos kontit on luotu ennen kirjautumisen lisäämistä, tietokanta on päivitettävä: ks.
[Päivitys olemassa olevaan asennukseen](#päivitys-olemassa-olevaan-asennukseen).

## Käyttö

### Kirjautuminen
- Kaikki sivut vaativat kirjautumisen; kirjautumaton käyttäjä ohjataan kirjautumissivulle
- Yläpalkista löytyvät **Oma tili** (salasanan vaihto) ja **Kirjaudu ulos**
- Omistajalle näkyy lisäksi **Käyttäjät**-sivu, jolla hallitaan käyttäjätilejä

### Kotisivu
- Näyttää 10 viimeisimmät tapahtumat
- Navigointivalikko muihin osioihin

### Lisää tapahtuma
- Päivämäärä: Valitse päivämäärä
- Tyyppi: Tulo tai meno
- Kategoria: 
  - Tulo
  - Yleinen meno
  - Matkalasku
  - Puhelin ja tietoliikenne
- Kuvaus: Lyhyt kuvaus tapahtumasta
- Summa: Summa euroissa
- ALV-prosentti: Oletus 24%, voit muuttaa

### Raportit
- **Yrityksen kannattavuus**: Kokonaistulot, menot ja tulos
- **Kvartaaliraportit**: Tulot ja menot neljännesvuosittain

### Veroilmoitukset
- **ALV-ilmoitus**: ALV-tiedot ja summat
- **Veroilmoitus**: Verotettava tulo
- **CSV-vienti**: Vie tiedot CSV-muodossa (`?export=vat` tai `?export=tax`). Vientityyppi validoidaan: vain nämä kaksi
  arvoa hyväksytään, ja mikä tahansa muu arvo antaa virheen HTTP 400 eikä mitään tietoja lähetetä

## Kirjautuminen ja käyttäjät

Sovellus vaatii kirjautumisen kaikilla sivuilla. Suojaus on oletuksena päällä: sivu on julkinen vain, jos se määrittelee
vakion `PUBLIC_PAGE` ennen `src/lib/bootstrap.php`:n lataamista (vain `login.php` tekee niin). Uusi sivu on siis suojattu,
vaikka tekijä unohtaisi erillisen tarkistuksen, ja tarkistus tehdään ennen sivun omaa koodia (esim. lomakkeen tallennusta).

### Ensimmäinen omistaja

Repossa ei ole oletustunnuksia. Ensimmäinen omistaja luodaan komentoriviltä (skripti ei toimi selaimesta):

```bash
docker compose exec web php cli/manage_users.php create --username=olavi --name="Olavi Esimerkki" --role=owner
```

Komento kysyy salasanan piilotettuna kahdesti. Ilman päätettä (esim. skriptistä) salasanan voi antaa vakiosyötteestä:

```bash
printf '%s\n' "$SALASANA" | docker compose exec -T web php cli/manage_users.php create --username=olavi --role=owner --password-stdin
```

Muut komennot: `list` (käyttäjälista) ja `reset-password --username=...` (hätäpalautus: asettaa uuden salasanan, ottaa tilin
käyttöön ja päättää sen avoimet istunnot). Jos yksikään omistajatili ei ole käytössä, luo uusi omistaja `create`-komennolla.
Muut käyttäjät omistaja luo selaimessa **Käyttäjät**-sivulla. Vanhemmissa Docker-asennuksissa komento on `docker-compose`.

### Roolit ja oikeudet

| Oikeus | Omistaja | Kirjanpitäjä |
|---|:---:|:---:|
| Etusivu, raportit ja veroilmoitukset | kyllä | kyllä |
| Tapahtumien lisääminen | kyllä | kyllä |
| CSV-vienti | kyllä | kyllä |
| Käyttäjien hallinta (luonti, rooli, käytöstä poisto, salasanan nollaus) | kyllä | ei |

Sivut tarkistavat *oikeuden* (`require_permission('manage_users')`), eivät roolin nimeä. Uuden roolin lisääminen vaatii vain
taulukon `ROLE_PERMISSIONS` ja nimen `ROLE_LABELS` (`src/lib/accounts.php`) sekä `users.role`-sarakkeen ENUM-määrityksen
päivityksen. Omistaja ei voi muokata omaa rooliaan tai tilaansa (ei voi lukita itseään ulos); oman salasanan voi vaihtaa
**Oma tili** -sivulla.

### Salasanat, istunto ja suojaukset

- **Salasanat**: tallennetaan vain bcrypt-tiivisteenä (`password_hash`, kustannus 12), ei koskaan selkotekstinä eikä lokiin.
  Pituus on 10–72 tavua (bcrypt ei käytä pidempää).
- **Istunto**: HttpOnly- ja SameSite=Lax-eväste, uusi istuntotunniste kirjautuessa (estää istuntoon kiinnittämisen),
  päättyy 30 minuutin toimettomuuteen ja viimeistään 8 tunnin kuluttua kirjautumisesta. Tili tarkistetaan tietokannasta
  joka pyynnöllä: käytöstä poistettu käyttäjä tai vaihdettu salasana päättää avoimet istunnot heti.
- **CSRF-suojaus**: kaikki lomakkeet (myös uloskirjautuminen ja tapahtuman lisäys) sisältävät istuntokohtaisen tunnisteen,
  joka tarkistetaan palvelimella ennen mitään muutoksia.
- **Yritysten rajoitus**: 5 epäonnistunutta yritystä samalla tunnuksella ja IP-osoitteesta tai 20 yritystä samasta
  IP-osoitteesta 15 minuutissa estää kirjautumisen väliaikaisesti (HTTP 429). Virheviesti on aina sama ja vastausaika
  samaa luokkaa, oli tunnus olemassa tai ei.
- **Otsakkeet**: sisältöturvakäytäntö (CSP), `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`,
  `Referrer-Policy: no-referrer` ja `Cache-Control: no-store`.
- **Loki**: tietoturvatapahtumat (kirjautumiset, epäonnistumiset, käyttäjämuutokset, estetyt pyynnöt) kirjataan palvelimen
  lokiin: `docker compose logs web`.

### Päivitys olemassa olevaan asennukseen

`schema.sql` ajetaan vain, kun tietokanta alustetaan ensimmäisen kerran. Jos tietokanta on luotu ennen kirjautumisen
lisäämistä, tauluja `users` ja `login_attempts` ei ole, ja kirjautumissivu kehottaa päivittämään tietokannan. Valitse toinen
tavoista:

- **Säilytä tiedot**: aja migraatio projektin juuressa (sen voi ajaa turvallisesti uudelleen) ja luo sen jälkeen
  ensimmäinen omistaja:

  ```bash
  docker compose exec -T db mysql -uuser -ppass taloushallinto < migrations/001_add_authentication.sql
  ```

  PowerShell:

  ```powershell
  cmd /c "docker compose exec -T db mysql -uuser -ppass taloushallinto < migrations\001_add_authentication.sql"
  ```

- **Aloita alusta**: `docker compose down` ja `docker compose up -d` luovat kontit ja tietokannan uudelleen uudella
  rakenteella. Esimerkkidata palautuu, mutta aiemmin lisätyt tapahtumat katoavat.

### Tuotantokäyttö ja rajoitukset

- Käytä HTTPS:ää (esim. käänteinen välityspalvelin, joka päättää TLS-yhteyden). HTTPS-pyynnöillä istuntoeväste saa
  `Secure`-lipun ja vastaukseen tulee `Strict-Transport-Security`-otsake. Jos TLS päätetään sovelluksen edessä, aseta
  ympäristömuuttuja `APP_FORCE_HTTPS=1`.
- Yritysten rajoitus perustuu IP-osoitteeseen (`REMOTE_ADDR`). `X-Forwarded-For`-otsakkeeseen ei luoteta, koska asiakas voi
  väärentää sen; välityspalvelimen takana kaikki asiakkaat näyttävät samalta IP-osoitteelta.
- Tietokannan oletustunnukset (`user`/`pass`, `root`/`root`) sekä portit 3306 ja 8081 on tarkoitettu vain paikalliseen
  kehitykseen: phpMyAdmin kirjautuu automaattisesti root-tunnuksilla ja näyttää myös salasanatiivisteet. Vaihda tunnukset
  ja sulje portit, ennen kuin sovellus avataan verkkoon.
- Kaksivaiheista tunnistautumista tai sähköpostipohjaista salasanan palautusta ei ole: palautus tehdään komentoriviltä.

## Ulkoasu

Käyttöliittymä noudattaa `design/`-kansion logoa ja wireframea (`wireframe_allpages.svg`): sininen navigaatiopalkki,
yhteenvetokortit ja paneelit. Tyylit on toteutettu omalla CSS:llä (`src/assets/style.css`) ilman ulkoisia kirjastoja,
jotta sovellus toimii myös ilman verkkoyhteyttä. Värit ja muodot on johdettu logosta ja wireframesta.

### Responsiivisuus

Käyttöliittymä mukautuu puhelimen, tabletin ja työpöydän näyttöihin. Sivupohja (`src/lib/layout.php`) määrittelee
`viewport`-metatiedon, ja `style.css`:n lopun media query -osio muuttaa asettelun alle 45 em (noin 720 px) leveillä
näytöillä: navigaatio muuttuu tasaleveäksi ruudukoksi, lomakkeet yhdeksi sarakkeeksi ja tapahtumataulukko korttilistaksi.
Erittäin kapealla näytöllä (alle 21 em, noin 336 px) sarakkeen nimi näytetään arvon yläpuolella. Navigaatiolinkit,
painikkeet ja lomakekentät ovat vähintään 44 px korkeita, eikä sivuilla ole vaakavieritystä missään 280–1920 px leveässä
näkymässä (tarkistettu selaimen viewport-emulaatiolla).

Toisenkin taulukon saa korttilistaksi lisäämällä `<table>`-elementille luokan `data--stack` ja jokaiselle solulle
`data-label`-attribuutin, jonka arvo on sarakkeen nimi (esimerkki: `src/index.php`). Solulle, jossa voi olla pitkiä
välilyönnittömiä merkkijonoja (kuten kuvaus), lisätään luokka `cell-wrap`.

## Tietokannan tunnukset

- **Host**: `db`
- **Tietokanta**: `taloushallinto`
- **Käyttäjä**: `user`
- **Salasana**: `pass`
- **Root-salasana**: `root`

## Lisäpalvelut

### phpMyAdmin
Voit hallita tietokantaa phpMyAdminilla osoitteessa: `http://localhost:8081`
- Käyttäjä: `root`
- Salasana: `root`

Huom. phpMyAdmin kirjautuu automaattisesti root-tunnuksilla ja näyttää myös käyttäjätaulun (salasanatiivisteet). Käytä sitä
vain paikallisesti.

## Tiedostorakenne

```
├── docker-compose.yml      # Docker-kokoonpano
├── Dockerfile              # PHP-konttin määritys
├── schema.sql              # Tietokannan rakenne
├── migrations/
│   └── 001_add_authentication.sql  # Päivitys vanhaan tietokantaan (kirjautuminen)
├── plan.md                 # Suunnitelma
├── README.md               # Tämä tiedosto
├── design/
│   ├── logo.svg            # Sovelluksen logo
│   └── wireframe_allpages.svg  # Sivujen wireframe (ulkoasun pohja)
└── src/
    ├── index.php           # Kotisivu
    ├── config.php          # Tietokantakonfiguraatio
    ├── add_transaction.php  # Tapahtumien lisääminen
    ├── reports.php         # Raportit
    ├── tax_reports.php     # Veroilmoitukset
    ├── login.php           # Kirjautuminen (ainoa julkinen sivu)
    ├── logout.php          # Uloskirjautuminen (vain POST)
    ├── account.php         # Oma tili: salasanan vaihto
    ├── users.php           # Käyttäjähallinta (omistaja): lista ja luonti
    ├── user_edit.php       # Käyttäjän muokkaus (omistaja)
    ├── cli/
    │   └── manage_users.php  # Käyttäjien luonti ja salasanan palautus komentoriviltä
    ├── assets/
    │   ├── style.css       # Jaetut tyylit
    │   └── logo.svg        # Logo (kopio design/-kansiosta)
    └── lib/
        ├── bootstrap.php   # Yhteinen alustus: otsakkeet, istunto, kirjautumisvaatimus
        ├── auth.php        # Kirjautuminen, istunnot, käyttöoikeudet, yritysten rajoitus
        ├── accounts.php    # Käyttäjätilit: roolit, oikeudet, validointi, kyselyt
        ├── passwords.php   # Salasanakäytäntö ja bcrypt-tiivistäminen
        ├── csrf.php        # CSRF-tunnisteet
        ├── helpers.php     # Apufunktiot (HTML-koodaus, muotoilut, uudelleenohjaus)
        └── layout.php      # Jaettu sivupohja (yläpalkki, navigaatio, käyttäjävalikko)
```

## Pysäyttäminen

Pysäytä kontit:
```bash
docker-compose down
```

Huom. `down` poistaa kontit, ja seuraava `up` alustaa tietokannan uudelleen: esimerkkidata palautuu, mutta lisätyt tapahtumat
ja luodut käyttäjät katoavat. Jos tiedot halutaan säilyttää, pysäytä kontit komennolla `docker compose stop` ja käynnistä ne
uudelleen komennolla `docker compose start`.

## Kehitys ja laajentaminen

Järjestelmä on suunniteltu laajennettavaksi. Tulevaisuuden ominaisuuksia:
- Edistyneemmät raportit ja kaaviot
- Pankki-integraatiot
- PDF-vienti

## Huomautuksia

- Järjestelmä käyttää SQLi-injektioiden estoon PDO-valmiita lauseita
- Esimerkkidata sisältyy tietokantaan testaamista varten
- Suositellaan säännöllisiä varmuuskopioita

## Tuki ja kehitys

Jos sinulla on kysymyksiä tai ehdotuksia, ota yhteyttä projektiin.
