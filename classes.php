<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$pdo = getPDO();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $klassekode = trim($_POST['klassekode'] ?? '');

    if ($klassekode === '') {
        $error = 'Fant ikke klassekode å slette.';
    } else {
        $studentCountStatement = $pdo->prepare('SELECT COUNT(*) AS total FROM STUDENT WHERE klassekode = :klassekode');
        $studentCountStatement->execute(['klassekode' => $klassekode]);
        $studentCount = (int) ($studentCountStatement->fetch()['total'] ?? 0);

        if ($studentCount > 0) {
            $error = 'Du kan ikke slette klassen, slett studentene først.';
        } else {
            try {
                $statement = $pdo->prepare('DELETE FROM KLASSE WHERE klassekode = :klassekode');
                $statement->execute(['klassekode' => $klassekode]);

                if ($statement->rowCount() === 0) {
                    $error = 'Klassen eksisterer ikke.';
                } else {
                    header('Location: classes.php?message=' . urlencode('Klassen ble slettet.'));
                    exit;
                }
            } catch (PDOException $pdoException) {
                $error = 'Kunne ikke slette klassen. Prøv igjen senere.';
            }
        }
    }
}

if (isset($_GET['message'])) {
    $message = trim((string) $_GET['message']);
}

$classes = $pdo->query(
    'SELECT klassekode, klassenavn, studiumkode FROM KLASSE ORDER BY klassekode',
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <title>Klasser</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="page">
        <nav>
            <a href="index.php">Tilbake til menyen</a>
            <a href="add_class.php">Legg til ny klasse</a>
            <a href="slett_klasse.php">Slett klasse</a>
        </nav>

        <div class="card">
            <h1>Klasser</h1>

            <?php if ($message !== ''): ?>
                <p class="notice success"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <p class="notice error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if (count($classes) === 0): ?>
                <p>Ingen klasser registrert ennå.</p>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Klassekode</th>
                        <th>Klassenavn</th>
                        <th>Studiumkode</th>
                        <th>Handlinger</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?= htmlspecialchars($class['klassekode'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($class['klassenavn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($class['studiumkode'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Er du sikker på at du vil slette denne klassen?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="klassekode" value="<?= htmlspecialchars($class['klassekode'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                    <button type="submit">Slett</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
