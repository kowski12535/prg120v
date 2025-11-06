<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$pdo = getPDO();

$error = '';
$values = [
    'klassekode' => '',
    'klassenavn' => '',
    'studiumkode' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['klassekode'] = strtoupper(trim($_POST['klassekode'] ?? ''));
    $values['klassenavn'] = trim($_POST['klassenavn'] ?? '');
    $values['studiumkode'] = strtoupper(trim($_POST['studiumkode'] ?? ''));

    if ($values['klassekode'] === '' || $values['klassenavn'] === '' || $values['studiumkode'] === '') {
        $error = 'Fyll ut alle feltene.';
    } else {
        $duplicateCheck = $pdo->prepare('SELECT COUNT(*) AS total FROM KLASSE WHERE klassekode = :klassekode');
        $duplicateCheck->execute(['klassekode' => $values['klassekode']]);
        $existing = $duplicateCheck->fetch();

        if (($existing['total'] ?? 0) > 0) {
            $error = 'Klassekoden finnes allerede. Velg en annen kode.';
        } else {
            try {
                $statement = $pdo->prepare(
                    'INSERT INTO KLASSE (klassekode, klassenavn, studiumkode) VALUES (:klassekode, :klassenavn, :studiumkode)',
                );
                $statement->execute([
                    'klassekode' => $values['klassekode'],
                    'klassenavn' => $values['klassenavn'],
                    'studiumkode' => $values['studiumkode'],
                ]);

                header('Location: classes.php?message=' . urlencode('Ny klasse ble lagt til.'));
                exit;
            } catch (PDOException $pdoException) {
                $error = 'Kunne ikke lagre klassen. Prøv igjen senere.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <title>Legg til klasse</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="page">
        <nav>
            <a href="index.php">Tilbake til menyen</a>
            <a href="classes.php">Vis klasser</a>
            <a href="slett_klasse.php">Slett klasse</a>
        </nav>

        <div class="card">
            <h1>Legg til klasse</h1>

            <?php if ($error !== ''): ?>
                <p class="notice error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>

            <form method="post" class="form-grid">
                <label>
                    Klassekode
                    <input name="klassekode" maxlength="10" value="<?= htmlspecialchars($values['klassekode'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
                </label>
                <label>
                    Klassenavn
                    <input name="klassenavn" maxlength="100" value="<?= htmlspecialchars($values['klassenavn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
                </label>
                <label>
                    Studiumkode
                    <input name="studiumkode" maxlength="10" value="<?= htmlspecialchars($values['studiumkode'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
                </label>
                <button type="submit">Lagre klasse</button>
            </form>
        </div>
    </div>
</body>
</html>
