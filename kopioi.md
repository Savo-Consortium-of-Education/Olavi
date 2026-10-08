# Projektin vieminen GitHubiin

Ohje siitä, miten tämä projekti (Docker-ympäristö, lähdekoodi yms.) viedään uuteen GitHub-repositorioon.

> **Huom:** `docker-compose.yml`-tiedostossa on tällä hetkellä vain kehityskäytön oletussalasanoja
> (esim. `root`, `pass`). Ne voi viedä repoon sellaisenaan. Jos myöhemmin lisäät oikeita
> salasanoja tai API-avaimia, laita ne `.env`-tiedostoon ja lisää `.env` `.gitignore`-tiedostoon,
> jotta ne eivät päädy versionhallintaan.

## 1. Luo tyhjä repo GitHubiin (selaimessa)

1. Mene osoitteeseen https://github.com/new
2. **Repository name**: esim. `pienyrittajan-taloushallinto`
3. **Älä** rastita "Add a README" tai muita alustusvaihtoehtoja (koska paikallinen projekti on jo olemassa)
4. Paina **Create repository**
5. Kopioi seuraavalla sivulla näkyvä osoite, esim.
   `https://github.com/KAYTTAJANIMESI/pienyrittajan-taloushallinto.git`

## 2. Alusta git paikallisessa kansiossa

Avaa PowerShell projektin juurikansiossa (`pienyrittajan_taloushallinto-main`) ja aja:

```powershell
git init
git add .
git commit -m "Initial commit: Docker-ymparisto ja projekti"
git branch -M main
```

## 3. Yhdistä paikallinen repo GitHubiin ja työnnä (push)

```powershell
git remote add origin https://github.com/KAYTTAJANIMESI/pienyrittajan-taloushallinto.git
git push -u origin main
```

Korvaa `KAYTTAJANIMESI` ja repon nimi omilla vastaavillasi.

### Kirjautuminen

Kun ajat `git push` ensimmäistä kertaa, Windowsin Git avaa todennäköisesti selainikkunan
GitHub-kirjautumista varten (Git Credential Manager). Kirjaudu sisään ja anna oikeudet — sen
jälkeen `git push` toimii jatkossa ilman uutta kirjautumista.

## 4. Myöhemmät muutokset

Kun teet myöhemmin muutoksia projektiin, riittää:

```powershell
git add .
git commit -m "Kuvaus muutoksesta"
git push
```
