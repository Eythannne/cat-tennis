<?php
// Signal envoyé par le navigateur quand on quitte une page de l'admin sans rester dans l'admin
// (voir le script dans admin_footer). auth.php coupera la session si aucune page admin
// n'est rechargée dans les ADMIN_DELAI_SORTIE secondes.
require_once 'auth.php';

// On ne tient compte que du signal de la DERNIÈRE page affichée : si une autre page admin
// a été chargée entre-temps (rechargement, navigation), ce signal arrivé en retard est ignoré.
if (($_POST['page'] ?? '') !== '' && ($_POST['page'] ?? '') === ($_SESSION['page_id'] ?? null)) {
    $_SESSION['quitte_a'] = time();
}
http_response_code(204);
