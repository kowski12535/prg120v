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
        $duplicateCheck = $pdo->prepare('SELECT COUNT(*) AS total FROM STUDENT WHERE brukernavn = :brukernavn');
        $duplicateCheck->execute(['brukernavn' => $values['brukernavn']]);
        $duplicateRow = $duplicateCheck->fetch();
        $duplicateTotal = 0;

        if (is_array($duplicateRow)) {
            $duplicateTotal = (int) ($duplicateRow['total'] ?? 0);
        }

        if ($duplicateTotal > 0) {
            $error = 'Brukernavnet er allerede i bruk.';
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
                $error = 'Kunne ikke lagre studenten. Prøv igjen senere.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <title>Legg til student</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="page">
        <nav>
            <a href="index.php">Tilbake til menyen</a>
            <a href="students.php">Vis studenter</a>
            <a href="slett_student.php">Slett student</a>
        </nav>

        <div class="card">
            <h1>Legg til student</h1>

            <?php if ($error !== ''): ?>
                <p class="notice error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if (count($classes) === 0): ?>
                <p>Du må registrere minst én klasse før du kan legge til studenter.</p>
            <?php else: ?>
                <form method="post" class="form-grid">
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
        </div>
    </div>
</body>
</html>
