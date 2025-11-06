<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$pdo = getPDO();

$message = '';
$error = '';
$selectedClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedClass = strtoupper(trim($_POST['klassekode'] ?? ''));

    if ($selectedClass === '') {
        $error = 'Velg en klasse å slette.';
    } else {
        $classStatement = $pdo->prepare('SELECT klassenavn FROM KLASSE WHERE klassekode = :klassekode');
        $classStatement->execute(['klassekode' => $selectedClass]);
        $class = $classStatement->fetch();

        if ($class === false) {
            $error = 'Klassen finnes ikke.';
        } else {
            $studentCountStatement = $pdo->prepare('SELECT COUNT(*) AS total FROM STUDENT WHERE klassekode = :klassekode');
            $studentCountStatement->execute(['klassekode' => $selectedClass]);
            $studentCount = (int) ($studentCountStatement->fetch()['total'] ?? 0);

            if ($studentCount > 0) {
                $error = 'Kan ikke slette klassen fordi den har registrerte studenter.';
            } else {
                try {
                    $deleteStatement = $pdo->prepare('DELETE FROM KLASSE WHERE klassekode = :klassekode');
                    $deleteStatement->execute(['klassekode' => $selectedClass]);

                    if ($deleteStatement->rowCount() === 0) {
                        $error = 'Klassen finnes ikke lenger.';
                    } else {
                        $message = 'Klassen ble slettet.';
                        $selectedClass = '';
                    }
                } catch (PDOException $pdoException) {
                    $error = 'Kunne ikke slette klassen. Prøv igjen senere.';
                }
            }
        }
    }
}

$classes = $pdo->query(
    'SELECT klassekode, klassenavn FROM KLASSE ORDER BY klassekode',
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <title>Slett klasse</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="page">
        <nav>
            <a href="index.php">Tilbake til menyen</a>
            <a href="classes.php">Vis klasser</a>
            <a href="add_class.php">Legg til klasse</a>
        </nav>

        <div class="card">
            <h1>Slett klasse</h1>

            <?php if ($message !== ''): ?>
                <p class="notice success"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <p class="notice error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if (count($classes) === 0): ?>
                <p>Det finnes ingen klasser å slette.</p>
            <?php else: ?>
                <form method="post" class="form-grid">
                    <label>
                        Klasse
                        <select name="klassekode" required>
                            <option value="">Velg klasse</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= htmlspecialchars($class['klassekode'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    <?= $selectedClass === $class['klassekode'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($class['klassekode'] . ' - ' . $class['klassenavn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit" onclick="return confirm('Er du sikker på at du vil slette denne klassen?');">Slett klasse</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
