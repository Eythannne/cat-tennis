<?php
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
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        // Pour l'instant on simule l'envoi — on branchera mail() plus tard
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $pageDesc ?>">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php
$activePage = 'contact';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
?>

<section class="page-hero">

<section class="page-hero">
    <div class="container">
        <h1>Contact</h1>
        <p>Une question ? Écrivez-nous</p>
    </div>
</section>

<section class="section-contact">
    <div class="container">
        <div class="contact-grid">

            <!-- Infos -->
            <div class="contact-info">
                <h2>Nous trouver</h2>
                <div class="info-block">
                    <span class="info-icon">📍</span>
                    <div>
                        <strong>Adresse</strong>
                        <p>Club Aulnaysien de Tennis<br>Aulnay-sous-Bois, 93600</p>
                    </div>
                </div>
                <div class="info-block">
                    <span class="info-icon">✉️</span>
                    <div>
                        <strong>Email</strong>
                        <p>contact@cat-tennis.fr</p>
                    </div>
                </div>
                <div class="info-block">
                    <span class="info-icon">🕐</span>
                    <div>
                        <strong>Secrétariat</strong>
                        <p>Lundi – Vendredi : 14h – 18h<br>Samedi : 9h – 12h</p>
                    </div>
                </div>
                <div class="info-block">
                    <span class="info-icon">🎾</span>
                    <div>
                        <strong>Réservation courts</strong>
                        <p><a href="https://www.ten-up.com" target="_blank" rel="noopener">Via Ten'up →</a></p>
                    </div>
                </div>
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
                                <option value="inscription" <?= ($_POST['sujet'] ?? '') === 'inscription' ? 'selected' : '' ?>>Inscription au club</option>
                                <option value="cours" <?= ($_POST['sujet'] ?? '') === 'cours' ? 'selected' : '' ?>>Cours & entraînements</option>
                                <option value="tournoi" <?= ($_POST['sujet'] ?? '') === 'tournoi' ? 'selected' : '' ?>>Tournois & compétitions</option>
                                <option value="reservation" <?= ($_POST['sujet'] ?? '') === 'reservation' ? 'selected' : '' ?>>Réservation de courts</option>
                                <option value="autre" <?= ($_POST['sujet'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
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
</body>
</html>