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
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="page">
        <nav>
            <a href="index.php">Tilbake til menyen</a>
            <a href="add_student.php">Legg til ny student</a>
        </nav>

        <div class="card">
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
        </div>
    </div>
</body>
</html>
