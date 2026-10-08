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

## Teknologia

- **Backend**: PHP 8.2 (Apache)
- **Tietokanta**: MySQL 8.0
- **Frontend**: HTML5, CSS3
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
5. Avaa selaimessa: `http://localhost:8080`

## Käyttö

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
- **CSV-vienti**: Vie tiedot CSV-muodossa

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

## Tiedostorakenne

```
├── docker-compose.yml      # Docker-kokoonpano
├── Dockerfile              # PHP-konttin määritys
├── schema.sql              # Tietokannan rakenne
├── plan.md                 # Suunnitelma
├── README.md               # Tämä tiedosto
└── src/
    ├── index.php           # Kotisivu
    ├── config.php          # Tietokantakonfiguraatio
    ├── add_transaction.php  # Tapahtumien lisääminen
    ├── reports.php         # Raportit
    └── tax_reports.php     # Veroilmoitukset
```

## Pysäyttäminen

Pysäytä kontit:
```bash
docker-compose down
```

## Kehitys ja laajentaminen

Järjestelmä on suunniteltu laajennettavaksi. Tulevaisuuden ominaisuuksia:
- Käyttäjien hallinta ja kirjautuminen
- Edistyneemmät raportit ja kaaviot
- Pankki-integraatiot
- PDF-vienti
- Mobiili-yhteensopivuus

## Huomautuksia

- Järjestelmä käyttää SQLi-injektioiden estoon PDO-valmiita lauseita
- Esimerkkidata sisältyy tietokantaan testaamista varten
- Suositellaan säännöllisiä varmuuskopioita

## Tuki ja kehitys

Jos sinulla on kysymyksiä tai ehdotuksia, ota yhteyttä projektiin.
