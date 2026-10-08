# Opiskelijan ohje: kloonaus, Docker ja koodin vieminen teams-1 -repoon

Tässä ohjeessa teet kolme asiaa:
1. Kloonaat projektin omalle koneelle.
2. Käynnistät projektin Dockerilla.
3. Viet oman työsi organisaation `teams-1` -repoon.

## 1) Esivaatimukset

Asenna koneellesi:
- [Git](https://git-scm.com/downloads)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)

Tarkista PowerShellissä:

```powershell
git --version
docker --version
docker compose version
```

## 2) Kloonaa projekti omalle koneelle

Kloonaa lähderepo:

```powershell
git clone https://github.com/Savo-Consortium-of-Education/pienyrittajan-taloushallinto.git
cd pienyrittajan-taloushallinto
```

## 3) Käynnistä projekti Dockerilla

Projektin juuressa (samassa kansiossa missä on `docker-compose.yml`) aja:

```powershell
docker compose up -d --build
```

Tämän jälkeen:
- Sovellus: http://localhost:8080
- phpMyAdmin: http://localhost:8081

Konttien pysäytys:

```powershell
docker compose down
```

## 4) Vie oma työ `teams-1` -repoon

### Vaihtoehto A (suositus): sinulla on jo kirjoitusoikeus `teams-1` -repoon

1. Siirry omaan paikalliseen projektikansioon:

```powershell
cd pienyrittajan-taloushallinto
```

2. Tarkista nykyinen remote:

```powershell
git remote -v
```

3. Vaihda `origin` osoittamaan `teams-1` -repoon: HUOM. tämä kuvitteellinen repo. Tee oma repo johon lähetät oman työsi.

```powershell
git remote set-url origin https://github.com/Savo-Consortium-of-Education/teams-1.git
```

4. Lähetä koodi:

```powershell
git add .
git commit -m "Initial import to teams-1"
git push -u origin main
```

**Jos ongelmia:**
- Hae etärepo:
  ```powershell
  git fetch origin
  ```
- Yhdistä muutokset:
  ```powershell
  git pull --rebase origin main
  ```

## 5) Jatkossa normaali työnkulku

Kun teet muutoksia:

```powershell
git add .
git commit -m "Kuvaus muutoksesta"
git push
```

## 6) Yleisimmät ongelmat

- **`Authentication failed`**  
  Kirjaudu GitHubiin selaimen kautta, kun Git Credential Manager pyytää.

- **`failed to solve` Docker buildissä**  
  Varmista, että Docker Desktop on käynnissä.

- **Portti varattu (`8080` tai `8081`)**  
  Sulje porttia käyttävä ohjelma tai muuta portit `docker-compose.yml`-tiedostossa.

## 7) Miten otat tiketin työn alle, teet välitallennukset ja palautat valmiin työn

### 7.1 Ota tiketti itsellesi
1. Avaa repon Issues-näkymä:  
   https://github.com/Savo-Consortium-of-Education/pienyrittajan-taloushallinto/issues
2. Valitse yksi issue (esim. `#5`).
3. Aseta issue itsellesi (**Assignees** -> oma käyttäjä).
4. Lisää kommentti: `Otan tämän työn alle.`

### 7.2 Luo oma branch tikettiä varten

Vaihda projektikansioon ja luo branch issue-numeron mukaan:

```powershell
git checkout main
git pull
git checkout -b issue-5-haku-ja-suodatus
```

> Vaihda branchin nimi aina oman issuensa mukaan (esim. `issue-10-xss-korjaus`).

### 7.3 Tee välitallennukset (myös ennen ruokataukoa)

Tee commit ja push säännöllisesti, vaikka työ ei olisi vielä valmis:

```powershell
git add .
git commit -m "WIP: issue #5 hakulomakkeen pohja"
git push -u origin issue-5-haku-ja-suodatus
```

Kun lähdet tauolle (esim. ruokailemaan), tee aina vähintään:
1. `git add .`
2. `git commit -m "WIP: issue #X välitallennus ennen taukoa"`
3. `git push`

Näin työ ei katoa, vaikka kone sammuu tai yhteys katkeaa.

### 7.4 Palauta valmis työ

Kun issue on valmis:
1. Tee lopullinen commit:

```powershell
git add .
git commit -m "Fix #5: lisää haku ja suodatus tapahtumille"
git push
```

2. Avaa Pull Request branchista `main`-haaraan.
3. Kirjoita PR-kuvaukseen `Fixes #5` (tai oikea issue-numero).  
   Tämä sulkee issuen automaattisesti, kun PR mergetään.
4. Pyydä review opettajalta/tiimiltä.
5. Lisää issueen kommentti: `Työ valmis, PR avattu: <PR-linkki>`.

> **Huom:** Kohdat 2-5 tehdään GitHubissa selaimen kautta (web-käyttöliittymässä), ei paikallisessa terminaalissa.

### 7.5 Mitä merge tarkoittaa (tarkemmat ohjeet)

**Merge** tarkoittaa sitä, että sinun branchissasi olevat muutokset yhdistetään projektin
`main`-haaraan. Vasta merge-vaiheen jälkeen korjauksesi ovat mukana projektin pääversiossa.

Käytännössä GitHubissa:
1. Avaa oma Pull Request.
2. Varmista, että tarkistukset (checks) ovat vihreitä ja review on hyväksytty.
3. Paina **Merge pull request** (tai **Squash and merge**, jos opettaja/tiimi käyttää sitä).
4. Vahvista merge.
5. Tarkista, että PR:n tila muuttuu `Merged`-tilaan ja issue sulkeutuu (jos käytit `Fixes #...`).

### 7.6 Mergen jälkeen (paikallinen kone)

Kun PR on hyväksytty ja mergetty, päivitä oma paikallinen reposi:

```powershell
git checkout main
git pull
git branch -d issue-5-haku-ja-suodatus
```

## 8) Miten opiskelija saa GitHub Copilot Pron käyttöön

> GitHubin opiskelijaetu voi sisältää Copilot Pron. Etujen sisältö voi muuttua, joten tarkista aina ajantasainen tieto GitHubin sivuilta.

### 8.1 Luo GitHub-tili
1. Mene osoitteeseen https://github.com/signup
2. Luo tili koulun sähköpostilla (suositus).
3. Vahvista sähköposti.

### 8.2 Hae opiskelijaetua (GitHub Education)
1. Mene osoitteeseen https://education.github.com/pack
2. Paina **Get student benefits** / **Sign up**.
3. Täytä opiskelijatiedot ja oppilaitos.
4. Tee henkilöllisyys/opiskelijastatuksen vahvistus (esim. opiskelijakortti tai oppilaitoksen sähköposti).
5. Lähetä hakemus ja odota hyväksyntää.

### 8.3 Ota Copilot Pro käyttöön
1. Kun opiskelijaetu on hyväksytty, avaa Copilot-sivu: https://github.com/features/copilot
2. Aktivoi Copilot Pro opiskelijaedun kautta.
3. Tarkista tilauksen tila: **GitHub Settings -> Billing and plans**.

### 8.4 Ota Copilot käyttöön VS Codessa
1. Asenna laajennus: **GitHub Copilot** (ja halutessasi **GitHub Copilot Chat**).
2. Kirjaudu VS Codessa sisään samalla GitHub-tilillä.
3. Varmista, että Copilot on päällä VS Coden asetuksissa.
4. Testaa kirjoittamalla kommentti tai funktion alku ja katso ehdotukset.

### 8.5 Jos aktivointi ei onnistu
- Tarkista, että opiskelijaetu on oikeasti hyväksytty (ei vain hakemus lähetetty).
- Tarkista, että olet kirjautunut oikealle GitHub-tilille VS Codessa.
- Kirjaudu ulos/sisään GitHubista VS Codessa ja käynnistä VS Code uudelleen.
- Katso GitHubin ohje: https://docs.github.com/en/copilot
