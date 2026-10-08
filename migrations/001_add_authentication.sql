-- Migraatio 001: kirjautuminen ja käyttäjähallinta (issue #3).
--
-- Uusissa asennuksissa tämä rakenne tulee valmiiksi tiedostosta schema.sql. Tätä tiedostoa tarvitaan vain, kun
-- tietokanta on luotu ennen kirjautumisen lisäämistä ja tapahtumatiedot halutaan säilyttää. Tiedoston voi ajaa
-- turvallisesti uudelleen (IF NOT EXISTS). Pidä rakenne samana kuin schema.sql:ssä.
--
-- Käyttö projektin juuressa:
--   Bash / Git Bash:  docker compose exec -T db mysql -uuser -ppass taloushallinto < migrations/001_add_authentication.sql
--   PowerShell:       cmd /c "docker compose exec -T db mysql -uuser -ppass taloushallinto < migrations\001_add_authentication.sql"
-- Tämän jälkeen luo ensimmäinen omistaja komentoriviltä (ks. README.md).

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    role ENUM('owner', 'accountant') NOT NULL DEFAULT 'accountant',
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    session_version INT NOT NULL DEFAULT 1, -- kasvaa salasanan vaihdossa: tilin vanhat istunnot lakkaavat toimimasta
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attempts_ip_time (ip_address, attempted_at)
);
