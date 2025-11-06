<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$pdo = getPDO();

$message = '';
$error = '';
$selectedUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedUsername = strtolower(trim($_POST['brukernavn'] ?? ''));

    if ($selectedUsername === '') {
        $error = 'Velg en student å slette.';
    } else {
        $studentStatement = $pdo->prepare(
            'SELECT fornavn, etternavn FROM STUDENT WHERE brukernavn = :brukernavn',
        );
        $studentStatement->execute(['brukernavn' => $selectedUsername]);
        $student = $studentStatement->fetch();

        if ($student === false) {
            $error = 'Studenten finnes ikke.';
        } else {
            try {
                $deleteStatement = $pdo->prepare('DELETE FROM STUDENT WHERE brukernavn = :brukernavn');
                $deleteStatement->execute(['brukernavn' => $selectedUsername]);

                if ($deleteStatement->rowCount() === 0) {
                    $error = 'Studenten finnes ikke lenger.';
                } else {
                    $message = 'Studenten ble slettet.';
                    $selectedUsername = '';
                }
            } catch (PDOException $pdoException) {
                $error = 'Kunne ikke slette studenten. Prøv igjen senere.';
            }
        }
    }
}

$students = $pdo->query(
    'SELECT brukernavn, fornavn, etternavn FROM STUDENT ORDER BY etternavn, fornavn',
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <title>Slett student</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="page">
        <nav>
            <a href="index.php">Tilbake til menyen</a>
            <a href="students.php">Vis studenter</a>
            <a href="add_student.php">Legg til student</a>
        </nav>

        <div class="card">
            <h1>Slett student</h1>

            <?php if ($message !== ''): ?>
                <p class="notice success"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <p class="notice error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if (count($students) === 0): ?>
                <p>Det finnes ingen studenter å slette.</p>
            <?php else: ?>
                <form method="post" class="form-grid">
                    <label>
                        Student
                        <select name="brukernavn" required>
                            <option value="">Velg student</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= htmlspecialchars($student['brukernavn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    <?= $selectedUsername === $student['brukernavn'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(
                                        $student['brukernavn'] . ' - ' . $student['fornavn'] . ' ' . $student['etternavn'],
                                        ENT_QUOTES | ENT_SUBSTITUTE,
                                        'UTF-8',
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit" onclick="return confirm('Er du sikker på at du vil slette denne studenten?');">Slett student</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
