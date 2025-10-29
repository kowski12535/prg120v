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
            $error = 'Kunne ikke lagre klassen. Sjekk om klassekoden er unik.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <title>Legg til klasse</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 2rem auto; }
        form { display: grid; gap: 1rem; }
        label { display: grid; gap: 0.3rem; }
        input { padding: 0.4rem; font-size: 1rem; }
        button { padding: 0.5rem 1rem; font-size: 1rem; }
        .notice { margin-bottom: 1rem; padding: 0.75rem; border-radius: 4px; }
        .error { background-color: #f4cccc; border: 1px solid #e06666; }
        nav a { margin-right: 1rem; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Tilbake til menyen</a>
        <a href="classes.php">Vis klasser</a>
    </nav>

    <h1>Legg til klasse</h1>

    <?php if ($error !== ''): ?>
        <p class="notice error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post">
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
</body>
</html>
