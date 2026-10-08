Oppilas kopioi issueen omaan repoonsa
Avaa lähderepon issue: https://github.com/Savo-Consortium-of-Education/pienyrittajan-taloushallinto/issues
Klikkaa yhtä issuea auki.
Valitse otsikosta ja kuvauksesta tärkeimmät tiedot.
Mene omaan repoon ja luo uusi issue.
Liitä siihen sama otsikko ja kuvaus.
Lisää alkuun tai loppuun rivi: Alkuperäinen issue: https://github.com/Savo-Consortium-of-Education/pienyrittajan-taloushallinto/issues/XX
Tee tehtävä oman repon koodiin.



# Pienyrityksen taloushallintojärjestelmän ongelmat

## Ongelma 1: Perus ulkoasu ilman modernia suunnittelua

**Kuvaus:**  
Sovelluksen ulkoasu on hyvin yksinkertainen ja vanhanaikainen. Käytetään inline-CSS:ää ja perus HTML-elementtejä ilman moderneja UI-komponentteja tai visuaalista hierarkiaa.

**Toistettavat vaiheet:**  
1. Katso mitä tahansa sivua sovelluksessa.  
2. Huomaa yksinkertainen fontti, värit ja layout.

**Odotettu toiminta:**  
Ulkoasu pitäisi päivittää modernilla CSS-frameworkilla (kuten Bootstrap tai Tailwind) tai mukautetulla tyylillä, joka sisältää paremman typografian, värit ja visuaalisen hierarkian. Käytä design -kansiossa olevaa logoa ja wireframeja.

**Todellinen toiminta:**  
Perus HTML ja inline-CSS ilman suunnittelua.

**Vakavuus:** Matala  
**Prioriteetti:** Matala  
**Tunnisteet:** ulkoasu, käyttöliittymä, suunnittelu




## Ongelma 2: Ei responsiivista suunnittelua mobiililaitteille

**Kuvaus:**  
Sovelluksen käyttöliittymä ei ole responsiivinen, mikä tekee siitä vaikean käyttää mobiililaitteilla. Taulukot ja lomakkeet eivät skaalaudu oikein pienillä näytöillä.

**Toistettavat vaiheet:**  
1. Avaa sovellus mobiililaitteella tai pienennä selainikkunaa.  
2. Huomaa, että taulukot ylittävät näytön reunat ja navigaatio on hankala.

**Odotettu toiminta:**  
Käyttöliittymän pitäisi mukautua eri näyttökokoihin käyttäen CSS-media queryjä ja responsiivisia taulukoita.

**Todellinen toiminta:**  
Kiinteä leveys ja perus-CSS ilman responsiivisuutta.

**Vakavuus:** Keskitaso  
**Prioriteetti:** Keskitaso  
**Tunnisteet:** ulkoasu, responsiivisuus, mobiili

## Ongelma 3: Ei autentikointia tai käyttäjien hallintaa

**Kuvaus:**  
Sovelluksessa ei ole minkäänlaista autentikointia tai käyttäjien hallintaa. Kuka tahansa voi käyttää sovellusta ja muokata tietoja ilman kirjautumista.

**Toistettavat vaiheet:**  
1. Avaa sovellus suoraan selaimessa.  
2. Huomaa, ettei ole kirjautumissivua tai käyttäjän vahvistusta.

**Odotettu toiminta:**  
Sovelluksen pitäisi vaatia kirjautuminen ja mahdollisesti tukea useita käyttäjiä rooleineen.

**Todellinen toiminta:**  
Ei autentikointia, kaikki pääsevät käsiksi kaikkiin toimintoihin.

**Vakavuus:** Korkea  
**Prioriteetti:** Korkea  
**Tunnisteet:** turvallisuus, autentikointi, käyttäjähallinta

## Ongelma 4: Ei GET-parametrien validointia vientitoiminnossa

**Kuvaus:**  
Tiedostossa `tax_reports.php` vienti käyttää `$_GET['export']`-parametria ilman validointia. Vaikka vain tietyt arvot toimivat, tämä voi johtaa virheisiin tai mahdollistaa epätoivotun käytön.

**Toistettavat vaiheet:**  
1. Muokkaa URL:ia lisäämällä ?export=invalid.  
2. Huomaa, ettei validointia tapahdu.

**Odotettu toiminta:**  
GET-parametrit pitäisi validoida ja sallia vain tunnetut arvot.

**Todellinen toiminta:**  
Parametri otetaan suoraan käyttöön.

**Vakavuus:** Keskitaso  
**Prioriteetti:** Keskitaso  
**Tunnisteet:** validointi, turvallisuus, vienti

## Ongelma 5: Ei hakua tai suodatusta tapahtumille

**Kuvaus:**  
Sovelluksessa ei ole hakutoimintoa tai suodatusta tapahtumille. Käyttäjät eivät voi etsiä tiettyjä tapahtumia kuvauksen, päivämäärän tai kategorian perusteella.

**Toistettavat vaiheet:**  
1. Katso tapahtumalistaa.  
2. Huomaa, ettei ole hakukenttää tai suodattimia.

**Odotettu toiminta:**  
Pitäisi olla hakutoiminto ja suodattimet päivämäärän, kategorian ja summan mukaan.

**Todellinen toiminta:**  
Vain viimeisimmät 10 tapahtumaa näytetään ilman hakua.

**Vakavuus:** Keskitaso  
**Prioriteetti:** Keskitaso  
**Tunnisteet:** käyttöliittymä, toiminnallisuus, haku

## Ongelma 6: Ei kaavioita tai visualisointeja raporteissa

**Kuvaus:**  
Raportit näyttävät vain numeroita taulukoissa ilman kaavioita tai visualisointeja, mikä tekee tiedon ymmärtämisestä vaikeampaa.

**Toistettavat vaiheet:**  
1. Avaa raporttisivu.  
2. Huomaa, ettei ole kaavioita tuloista, menoista tai voitoista.

**Odotettu toiminta:**  
Raporttien pitäisi sisältää kaavioita (esim. pylväsdiagrammeja) tulojen ja menojen visualisoimiseksi.

**Todellinen toiminta:**  
Vain tekstimuotoiset numerot ja taulukot.

**Vakavuus:** Matala  
**Prioriteetti:** Matala  
**Tunnisteet:** raportointi, visualisointi, käyttöliittymä

## Ongelma 7: Kovakoodatut tietokantatunnukset konfiguraatiotiedostossa

**Kuvaus:**  
Tietokantatunnukset ovat kovakoodattuja tiedostossa `config.php` käyttäen `define()`-lauseita. Tämä on turvallisuusriski, koska arkaluonteiset tiedot ovat näkyvissä lähdekoodissa.

**Toistettavat vaiheet:**  
1. Avaa `src/config.php`.  
2. Huomaa kovakoodatut DB_USER ja DB_PASS.

**Odotettu toiminta:**  
Tunnukset pitäisi ladata ympäristömuuttujista tai turvallisesta konfiguraatiotiedostosta, jota ei sitoudeta versionhallintaan.

**Todellinen toiminta:**  
Tunnukset ovat kovakoodattuja.

**Vakavuus:** Kriittinen  
**Prioriteetti:** Korkea  
**Tunnisteet:** turvallisuus, konfiguraatio

## Ongelma 8: Ei syötteen validointia tai puhdistusta lomakkeissa

**Kuvaus:**  
Tiedostossa `add_transaction.php` käyttäjän syötteet käytetään suoraan tietokantakyselyissä ilman validointia tai puhdistusta. Tämä voi johtaa virheellisiin tietoihin tai mahdollisiin turvallisuusongelmiin, vaikka valmiita lauseita käytetäänkin.

**Toistettavat vaiheet:**  
1. Lähetä lomake virheellisillä tiedoilla (esim. negatiivinen summa, virheellinen päivämäärä).  
2. Huomaa, ettei validointia tapahdu.

**Odotettu toiminta:**  
Syötteet pitäisi validoida (esim. positiiviset summat, kelvolliset päivämäärät) ja puhdistaa.

**Todellinen toiminta:**  
Ei validointia.

**Vakavuus:** Keskitaso  
**Prioriteetti:** Keskitaso  
**Tunnisteet:** validointi, turvallisuus

## Ongelma 9: Ei virheenkäsittelyä tietokantakyselyille

**Kuvaus:**  
Koko sovelluksessa tietokantakyselyt eivät sisällä asianmukaista virheenkäsittelyä. Jos kysely epäonnistuu, sovellus voi kaatua tai näyttää virheitä käyttäjille.

**Toistettavat vaiheet:**  
1. Katkaise väliaikaisesti tietokantayhteys.  
2. Lataa mikä tahansa sivu, joka tekee tietokantakyselyn.  
3. Huomaa käsittelemättömät poikkeukset.

**Odotettu toiminta:**  
Virheet pitäisi kirjata lokiin ja näyttää käyttäjäystävällisiä viestejä.

**Todellinen toiminta:**  
PDO-poikkeukset voivat heittää suoraan.

**Vakavuus:** Keskitaso  
**Prioriteetti:** Keskitaso  
**Tunnisteet:** virheenkäsittely, tietokanta



## Ongelma 10: Mahdolliset XSS-haavoittuvuudet tulosteessa

**Kuvaus:**  
Tiedostoissa `index.php` ja muissa tiedostoissa tietokannasta tulevat tiedot tulostetaan suoraan HTML:ään ilman koodausta. Jos käyttäjän syöte sisältää HTML/JavaScript-koodia, se voi johtaa XSS-hyökkäyksiin.

**Toistettavat vaiheet:**  
1. Lisää tapahtuma kuvauksella, joka sisältää `<script>alert('xss')</script>`.  
2. Katso tapahtumalistaa.  
3. Skripti voi suorittaa.

**Odotettu toiminta:**  
Tuloste pitäisi koodata käyttäen `htmlspecialchars()`-funktiota.

**Todellinen toiminta:**  
Tiedot tulostetaan raakana.

**Vakavuus:** Korkea  
**Prioriteetti:** Korkea  
**Tunnisteet:** turvallisuus, xss

## Ongelma 11: Raportit eivät huomioi vuotta

**Kuvaus:**  
Tiedostossa `reports.php` kvartaaliraportit kysyvät kuukauden mukaan ilman vuoden määrittämistä, joten ne sisältävät tietoja kaikista vuosista.

**Toistettavat vaiheet:**  
1. Lisää tapahtumia eri vuosilta.  
2. Katso kvartaaliraportteja.  
3. Kaikkien vuosien tiedot yhdistetään.

**Odotettu toiminta:**  
Raporttien pitäisi suodattaa nykyisen vuoden mukaan tai sallia vuoden valinta.

**Todellinen toiminta:**  
Kaikki historiatiedot sisältyvät.

**Vakavuus:** Keskitaso  
**Prioriteetti:** Keskitaso  
**Tunnisteet:** raportointi, päivämääräsuodatus

## Ongelma 12: CSV-viennin tiedostonimen injektioriski

**Kuvaus:**  
Tiedostossa `tax_reports.php` vientitiedoston nimi on kovakoodattu, mutta jos se olisi dynaaminen, se voisi olla haavoittuva header-injektiolle. Tällä hetkellä turvallinen, mutta malli on riskialtis.

**Toistettavat vaiheet:**  
1. Tarkista CSV-viennin header-asetus.

**Odotettu toiminta:**  
Tiedostonimet pitäisi puhdistaa.

**Todellinen toiminta:**  
Kovakoodattu, mutta malli voisi parantaa.

**Vakavuus:** Matala  
**Prioriteetti:** Matala  
**Tunnisteet:** turvallisuus, vienti

## Ongelma 13: Ei sivutusta tapahtumalistauksessa

**Kuvaus:**  
Tiedostossa `index.php` vain viimeisimmät 10 tapahtumaa näytetään `LIMIT 10`:llä, mutta ei ole sivutusta useammille tapahtumille.

**Toistettavat vaiheet:**  
1. Lisää yli 10 tapahtumaa.  
2. Vain 10 näytetään, ei tapaa nähdä vanhempia.

**Odotettu toiminta:**  
Sivutus- tai lataa lisää -toiminto.

**Todellinen toiminta:**  
Vain viimeisimmät 10 näytetään.

**Vakavuus:** Matala  
**Prioriteetti:** Matala  
**Tunnisteet:** käyttöliittymä, sivutus

## Ongelma 14: Kategoria-enum sisältää tarpeettoman 'income'-kategorian

**Kuvaus:**  
Tiedostossa `schema.sql` kategoria-enum sisältää 'income'-kategorian tulotapahtumille, mutta koska tyyppi jo erottaa tulot/menot, tämä on tarpeetonta ja voisi yksinkertaistaa.

**Toistettavat vaiheet:**  
1. Tarkista ENUM-määrittely.

**Odotettu toiminta:**  
Kategorioiden pitäisi olla spesifisempiä, kuten 'myynti', 'palvelut' tuloille.

**Todellinen toiminta:**  
'income' käytetään kategoriana income-tyypille.

**Vakavuus:** Matala  
**Prioriteetti:** Matala  
**Tunnisteet:** tietokanta, skeema

## Ongelma 15: Virheellinen ALV-summan laskenta tapahtuman lisäämisessä

**Kuvaus:**  
ALV-summan laskenta tiedostossa `add_transaction.php` on virheellinen. Nykyinen kaava `$vat_amount = ($amount * $vat_rate / 100) / (1 + $vat_rate / 100)` olettaa, että syötetty summa sisältää ALV:n, mutta `schema.sql`:n esimerkkidatan perusteella summa näyttää olevan netto-summa, ja ALV pitäisi laskea kaavalla `$vat_amount = $amount * $vat_rate / 100`.

**Toistettavat vaiheet:**  
1. Lisää tapahtuma summalla 100 ja ALV-prosentilla 24.  
2. Tarkista laskettu ALV-summa.  
3. Se on noin 19,35 eikä 24.

**Odotettu toiminta:**  
ALV-summan pitäisi olla 24 summalle 100 24 %:n ALV:lla.

**Todellinen toiminta:**  
ALV-summa lasketaan noin 19,35:ksi.

**Vakavuus:** Korkea  
**Prioriteetti:** Korkea  
**Tunnisteet:** bugi, laskenta, alv



## Ongelma 16: Ei CSRF-suojausta lomakkeissa

**Kuvaus:**  
Tapahtuman lisäyslomake tiedostossa `add_transaction.php` ei sisällä CSRF-suojausta, mikä tekee siitä haavoittuvan cross-site request forgery -hyökkäyksille.

**Toistettavat vaiheet:**  
1. Tarkista lomakkeen HTML.  
2. CSRF-tokeneita ei ole läsnä.

**Odotettu toiminta:**  
Lomakkeiden pitäisi sisältää CSRF-tokenit.

**Todellinen toiminta:**  
Ei CSRF-suojausta.

**Vakavuus:** Korkea  
**Prioriteetti:** Korkea  
**Tunnisteet:** turvallisuus, csrf





