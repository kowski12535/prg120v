<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$pdo = getPDO();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $brukernavn = trim($_POST['brukernavn'] ?? '');

    if ($brukernavn === '') {
        $error = 'Fant ikke brukernavn å slette.';
    } else {
        $statement = $pdo->prepare('DELETE FROM STUDENT WHERE brukernavn = :brukernavn');
        $statement->execute(['brukernavn' => $brukernavn]);

        if ($statement->rowCount() === 0) {
            $error = 'Studenten finnes ikke.';
        } else {
            header('Location: students.php?message=' . urlencode('Studenten ble slettet.'));
            exit;
        }
    }
}

if (isset($_GET['message'])) {
    $message = trim((string) $_GET['message']);
}

$students = $pdo->query(
    'SELECT s.brukernavn, s.fornavn, s.etternavn, s.klassekode, k.klassenavn
     FROM STUDENT s
     LEFT JOIN KLASSE k ON k.klassekode = s.klassekode
     ORDER BY s.etternavn, s.fornavn',
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <title>Studenter</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 2rem auto; }
        table { border-collapse: collapse; width: 100%; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: 0.5rem 0.75rem; text-align: left; }
        th { background-color: #f0f0f0; }
        .notice { margin-top: 1rem; padding: 0.75rem; border-radius: 4px; }
        .success { background-color: #d9ead3; border: 1px solid #93c47d; }
        .error { background-color: #f4cccc; border: 1px solid #e06666; }
        nav a { margin-right: 1rem; }
        form { margin: 0; }
        button { padding: 0.35rem 0.75rem; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Tilbake til menyen</a>
        <a href="add_student.php">Legg til ny student</a>
    </nav>

    <h1>Studenter</h1>

    <?php if ($message !== ''): ?>
        <p class="notice success"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p class="notice error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (count($students) === 0): ?>
        <p>Ingen studenter registrert ennå.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Brukernavn</th>
                <th>Fornavn</th>
                <th>Etternavn</th>
                <th>Klassekode</th>
                <th>Klassenavn</th>
                <th>Handlinger</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['brukernavn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($student['fornavn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($student['etternavn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($student['klassekode'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($student['klassenavn'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Er du sikker på at du vil slette denne studenten?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="brukernavn" value="<?= htmlspecialchars($student['brukernavn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
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
