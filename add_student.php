<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$pdo = getPDO();

$classes = $pdo->query(
    'SELECT klassekode, klassenavn FROM KLASSE ORDER BY klassekode',
)->fetchAll();

$error = '';
$values = [
    'brukernavn' => '',
    'fornavn' => '',
    'etternavn' => '',
    'klassekode' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['brukernavn'] = strtolower(trim($_POST['brukernavn'] ?? ''));
    $values['fornavn'] = trim($_POST['fornavn'] ?? '');
    $values['etternavn'] = trim($_POST['etternavn'] ?? '');
    $values['klassekode'] = trim($_POST['klassekode'] ?? '');

    if ($values['brukernavn'] === '' || $values['fornavn'] === '' || $values['etternavn'] === '' || $values['klassekode'] === '') {
        $error = 'Fyll ut alle feltene.';
    } elseif (!in_array($values['klassekode'], array_column($classes, 'klassekode'), true)) {
        $error = 'Velg en gyldig klasse.';
    } else {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO STUDENT (brukernavn, fornavn, etternavn, klassekode) VALUES (:brukernavn, :fornavn, :etternavn, :klassekode)',
            );
            $statement->execute([
                'brukernavn' => $values['brukernavn'],
                'fornavn' => $values['fornavn'],
                'etternavn' => $values['etternavn'],
                'klassekode' => $values['klassekode'],
            ]);

            header('Location: students.php?message=' . urlencode('Ny student ble lagt til.'));
            exit;
        } catch (PDOException $pdoException) {
            $error = 'Kunne ikke lagre studenten. Sjekk om brukernavnet er unikt.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <title>Legg til student</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 2rem auto; }
        form { display: grid; gap: 1rem; }
        label { display: grid; gap: 0.3rem; }
        input, select { padding: 0.4rem; font-size: 1rem; }
        button { padding: 0.5rem 1rem; font-size: 1rem; }
        .notice { margin-bottom: 1rem; padding: 0.75rem; border-radius: 4px; }
        .error { background-color: #f4cccc; border: 1px solid #e06666; }
        nav a { margin-right: 1rem; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Tilbake til menyen</a>
        <a href="students.php">Vis studenter</a>
    </nav>

    <h1>Legg til student</h1>

    <?php if ($error !== ''): ?>
        <p class="notice error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (count($classes) === 0): ?>
        <p>Du må registrere minst én klasse før du kan legge til studenter.</p>
    <?php else: ?>
        <form method="post">
            <label>
                Brukernavn
                <input name="brukernavn" maxlength="20" value="<?= htmlspecialchars($values['brukernavn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
            </label>
            <label>
                Fornavn
                <input name="fornavn" maxlength="50" value="<?= htmlspecialchars($values['fornavn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
            </label>
            <label>
                Etternavn
                <input name="etternavn" maxlength="50" value="<?= htmlspecialchars($values['etternavn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
            </label>
            <label>
                Klasse
                <select name="klassekode" required>
                    <option value="">Velg klasse</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= htmlspecialchars($class['klassekode'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            <?= $values['klassekode'] === $class['klassekode'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($class['klassekode'] . ' - ' . $class['klassenavn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">Lagre student</button>
        </form>
    <?php endif; ?>
</body>
</html>
