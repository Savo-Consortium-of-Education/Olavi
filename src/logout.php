<?php
require __DIR__ . '/lib/bootstrap.php';

// Uloskirjautuminen tehdään vain POST-pyynnöllä ja CSRF-tunnisteella, jotta ulkopuolinen sivu
// (esim. kuvan osoitteena oleva linkki) ei voi kirjata käyttäjää ulos.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    render_error_page(405, 'Virheellinen pyyntö', 'Uloskirjautuminen tehdään yläpalkin "Kirjaudu ulos" -painikkeella.');
}

csrf_verify();

auth_log('logout', current_user()['username']);
end_session();
flash_set('success', 'Olet kirjautunut ulos.');
redirect('login.php', 303);
