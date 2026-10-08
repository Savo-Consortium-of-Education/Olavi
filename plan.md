# Suunnitelma pienyrityksen taloushallintojärjestelmälle

Tämä on yksityiskohtainen suunnitelma pienyrityksen taloushallintojärjestelmän kehittämiseksi. Suunnitelma perustuu käyttäjän vaatimuksiin: menojen, tulojen, matkalaskujen sekä puhelin- ja tietoliikennekulujen syöttö, raportointi yrityksen kannattavuudesta ja kvartaaleittain menot ja tulot, sekä veroilmoitukset ja ALV-ilmoitukset siirtotiedostoineen. Järjestelmä toteutetaan web-pohjaisena sovelluksena, jotta se on helppo käyttää ja ylläpitää.

## 1. Yleinen kuvaus ja tavoitteet
- **Tavoitteet**: Tarjota yksinkertainen, käyttäjäystävällinen työkalu pienyrityksille taloushallinnon hallintaan. Järjestelmä mahdollistaa päivittäisten tapahtumien kirjaamisen, automaattisen raportoinnin ja verotukseen liittyvien asiakirjojen generoinnin.
- **Käyttäjät**: Pienyrityksen omistaja tai kirjanpitäjä, joka tarvitsee nopean tavan hallita taloutta ilman monimutkaisia järjestelmiä.
- **Tekninen lähestymistapa**: Web-sovellus PHP:llä ja MySQL-tietokannalla. Käyttöliittymä HTML/CSS/JavaScript (ilman raskaita frameworkkeja aluksi). Konttienhallinta Dockerilla kehityksen ja tuotannon helpottamiseksi.
- **Tietoturva**: Perustason suojaus (esim. SQL-injektion esto PDO:lla). Ei vielä monialgorimaista kirjautumista, mutta laajennettavissa myöhemmin.

## 2. Toiminnalliset vaatimukset
- **Tietojen syöttö**:
  - Lomake uusien tapahtumien lisäämiseksi.
  - Kentät: Päivämäärä, tyyppi (tulo/meno), kategoria (tulo, yleinen meno, matkalasku, puhelin/tietoliikenne), kuvaus, summa, ALV-prosentti.
  - Validointi: Pakolliset kentät, numeeriset arvot, päivämäärän muoto.
  - ALV-laskenta automaattisesti (summa * ALV-% / (1 + ALV-%)).
- **Raportointi**:
  - **Kannattavuusraportti**: Kokonaistulot, kokonaismenot, voittomarginaali (tulot - menot).
  - **Kvartaaliraportit**: Tulot ja menot kvartaaleittain (Q1-Q4), voittomarginaali per kvartaali.
  - Näyttö taulukoina ja kaavioina (jos laajennetaan, mutta aluksi teksti/taulukko).
- **Veroilmoitukset ja ALV**:
  - **ALV-ilmoitus**: Laskenta maksettavasta ALV:sta (tulojen ALV) ja vähennettävästä ALV:sta (menojen ALV), saldo.
  - **Veroilmoitus**: Verotettava tulo (tulot - menot).
  - **Siirtotiedostot**: CSV-vienti tapahtumista veroilmoituksille ja ALV-ilmoituksille (Suomen verottajan vaatimusten mukaisesti, mutta yksinkertaistettuna).
- **Muut ominaisuudet**:
  - Tapahtumien listaaminen (viimeisimmät tapahtumat kotisivulla).
  - Hakutoiminto tapahtumien mukaan (päivämäärä, kategoria).
  - Perustason virheenkäsittely (esim. tietokantavirheet).

## 3. Tekninen arkkitehtuuri
- **Teknologiapino**:
  - **Backend**: PHP 8.2 (Apache-palvelin), PDO tietokantayhteyksiin.
  - **Tietokanta**: MySQL 8.0.
  - **Frontend**: HTML5, CSS3, minimaalinen JavaScript (esim. lomakkeiden validointi).
  - **Konttienhallinta**: Docker Compose (web-palvelin + tietokanta).
- **Tietokantaskeema**:
  - Taulu: `transactions`
    - Kentät: id (auto-increment), date (DATE), type (ENUM: 'income', 'expense'), category (ENUM: 'income', 'general_expense', 'travel', 'phone_data'), description (VARCHAR), amount (DECIMAL), vat_rate (DECIMAL), vat_amount (DECIMAL), created_at (TIMESTAMP).
  - Indeksit: date, type, category nopeampien kyselyjen vuoksi.
  - Alustava data: Muutama esimerkkitapahtuma testausta varten.
- **Kansiorakenne**:
  - `/src`: PHP-tiedostot (index.php, add_transaction.php, reports.php, tax_reports.php, config.php).
  - `/init.sql`: Tietokannan alustusskripti.
  - `docker-compose.yml`: Konttien määritykset.
- **API/rajapinnat**: Ei ulkoisia API:ita aluksi, mutta laajennettavissa (esim. pankki-integraatio).

## 4. Käyttöliittymäsuunnitelma
- **Navigaatio**: Yksinkertainen valikko (Koti, Lisää tapahtuma, Raportit, Veroilmoitukset).
- **Kotisivu**: Tapahtumien lista (taulukko), linkit muihin sivuihin.
- **Lisää tapahtuma -sivu**: Lomake kenttineen, submit-nappi.
- **Raportit -sivu**: Kannattavuusluvut tekstinä, kvartaalitaulukko.
- **Veroilmoitukset -sivu**: ALV- ja verolaskelmat, CSV-vientinapit.
- **Tyylit**: Minimalistinen CSS (valkoinen tausta, siniset napit, taulukot rajattuina).

## 5. Tietojen käsittely ja laskelmat
- **ALV-laskenta**: Tapahtuman lisäyksessä lasketaan ALV-summa automaattisesti.
- **Raportit**: SQL-kyselyt SUM-funktiolla tyypin ja kategorian mukaan, kvartaalifiltterit MONTH(date) BETWEEN.
- **CSV-vienti**: PHP:n fputcsv-funktio tapahtumien vientiin (päivämäärä, tyyppi, kategoria, kuvaus, summa, ALV-% , ALV-summa).

## 6. Testaus ja validointi
- **Yksikkötestaus**: PHP:n sisäänrakennettu testaus tai PHPUnit myöhemmin.
- **Integraatiotestaus**: Lomakkeiden toiminta, tietokantakyselyt, CSV-vienti.
- **Käyttäjätestaus**: Manuaalinen testaus selaimessa (Chrome/Firefox).
- **Validointi**: Tarkista, että summat ovat oikein, päivämäärät valideja, ei SQL-injektioita.

## 7. Laajennusmahdollisuudet
- **Käyttäjien hallinta**: Kirjautuminen ja roolit (omistaja vs. kirjanpitäjä).
- **Edistynyt raportointi**: Kaaviot (Chart.js), PDF-vienti.
- **Integraatiot**: Pankki-API:t, Suomen verottajan rajapinnat.
- **Mobiili**: Responsiivinen design tai PWA.
- **Turvallisuus**: HTTPS, salaus, audit-logit.

## 8. Aikataulu ja resurssit
- **Vaihe 1**: Suunnitelma ja prototyyppi (1-2 päivää).
- **Vaihe 2**: Perustoteutus (syöttö, raportit, verot) (3-5 päivää).
- **Vaihe 3**: Testaus ja hiominen (1-2 päivää).
- **Resurssit**: PHP-kehitysympäristö, Docker, MySQL-työkalut. Ei ulkoisia kirjastoja aluksi.

Tämä suunnitelma on skaalautuva ja aloitetaan pienestä, toimivasta versiosta. Jos hyväksyt suunnitelman, voimme aloittaa toteutuksen vaiheittain.
