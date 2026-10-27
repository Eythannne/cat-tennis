<?php
require_once '../src/init.php';

$pageTitle = "Contact — CAT";
$pageDesc = "Contactez le Club Aulnaysien de Tennis à Aulnay-sous-Bois.";

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sujet = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$nom || !$email || !$sujet || !$message) {
        $error = 'Merci de remplir tous les champs.';
    } elseif (!isset(SUJETS_CONTACT[$sujet])) {
        $error = 'Sujet invalide.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO messages_contact (nom, email, sujet, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $sujet, $message]);
        $success = true;
    }
}

$nomClub   = param('nom_club', 'Club Aulnaysien de Tennis');
$adresse   = param('adresse', 'Aulnay-sous-Bois, 93600');
$emailClub = param('email_contact', 'contact@cat-tennis.fr');
$telephone = param('telephone');
$horaires  = param('horaires');

$extraHead = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/leaflet.css">
    <style>
        #map { height: 280px; border-radius: 10px; margin-top: 28px; z-index: 1; }
    </style>';
?>
<!DOCTYPE html>
<html lang="fr">
<?php include '../templates/head.php'; ?>
<body>

<?php
$activePage = 'contact';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Contact</h1>
        <p>Une question ? Écrivez-nous</p>
    </div>
</section>

<section class="section-contact">
    <div class="container">
        <div class="contact-grid">

            <!-- Infos + Carte -->
            <div class="contact-info">
                <h2>Nous trouver</h2>
                <div class="info-block">
                    <span class="info-icon">📍</span>
                    <div>
                        <strong>Adresse</strong>
                        <p><?= htmlspecialchars($nomClub) ?><br><?= htmlspecialchars($adresse) ?></p>
                    </div>
                </div>
                <div class="info-block">
                    <span class="info-icon">✉️</span>
                    <div>
                        <strong>Email</strong>
                        <p><a href="mailto:<?= htmlspecialchars($emailClub) ?>"><?= htmlspecialchars($emailClub) ?></a></p>
                    </div>
                </div>
                <?php if ($telephone): ?>
                <div class="info-block">
                    <span class="info-icon">📞</span>
                    <div>
                        <strong>Téléphone</strong>
                        <p><a href="tel:<?= htmlspecialchars(preg_replace('/[^\d+]/', '', $telephone)) ?>"><?= htmlspecialchars($telephone) ?></a></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($horaires): ?>
                <div class="info-block">
                    <span class="info-icon">🕐</span>
                    <div>
                        <strong>Secrétariat</strong>
                        <p><?= nl2br(htmlspecialchars($horaires)) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <div class="info-block">
                    <span class="info-icon">🎾</span>
                    <div>
                        <strong>Réservation courts</strong>
                        <p><a href="https://tenup.fft.fr/" target="_blank" rel="noopener">Via Ten'up →</a></p>
                    </div>
                </div>
                <div id="map"></div>
            </div>

            <!-- Formulaire -->
            <div class="contact-form-wrap">
                <?php if ($success): ?>
                    <div class="form-success">
                        ✅ Message envoyé ! Nous vous répondrons dans les plus brefs délais.
                    </div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="form-error">⚠️ <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="contact.php" class="contact-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom">Nom complet *</label>
                                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sujet">Sujet *</label>
                            <select id="sujet" name="sujet" required>
                                <option value="">-- Choisir un sujet --</option>
                                <?php foreach (SUJETS_CONTACT as $valeur => $label): ?>
                                <option value="<?= $valeur ?>" <?= ($_POST['sujet'] ?? '') === $valeur ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="6" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn-primary">Envoyer le message</button>
                    </form>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<?php include '../templates/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script src="assets/js/leaflet.js"></script>
<script>
    // Fix chemin des images markers (servis localement)
    L.Icon.Default.imagePath = 'assets/images/';
    const map = L.map('map').setView([48.9385, 2.4972], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);
    const icon = L.divIcon({
        html: '<div style="background:#1B6B2F;width:14px;height:14px;border-radius:50%;border:3px solid #F0C419;box-shadow:0 2px 6px rgba(0,0,0,0.4)"></div>',
        iconSize: [14, 14],
        iconAnchor: [7, 7],
        className: ''
    });
    L.marker([48.9385, 2.4972], { icon })
        .addTo(map)
        .bindPopup(<?= json_encode('<strong>' . htmlspecialchars($nomClub) . '</strong><br>' . htmlspecialchars($adresse)) ?>)
        .openPopup();
</script>
</body>
</html>