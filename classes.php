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
            $error = 'Kunne ikke slette klassen. Har den registrerte studenter?';
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
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 2rem auto; }
        table { border-collapse: collapse; width: 100%; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: 0.5rem 0.75rem; text-align: left; }
        th { background-color: #f0f0f0; }
        .notice { margin-top: 1rem; padding: 0.75rem; border-radius: 4px; }
        .success { background-color: #d9ead3; border: 1px solid #93c47d; }
        .error { background-color: #f4cccc; border: 1px solid #e06666; }
        .actions { display: flex; gap: 0.5rem; align-items: center; }
        nav a { margin-right: 1rem; }
        form { margin: 0; }
        button { padding: 0.35rem 0.75rem; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Tilbake til menyen</a>
        <a href="add_class.php">Legg til ny klasse</a>
    </nav>

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
</body>
</html>
