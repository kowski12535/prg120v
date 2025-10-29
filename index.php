<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <title>Student- og klasseroversikt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 2rem auto;
            line-height: 1.5;
        }
        h1 {
            text-align: center;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            margin: 0.75rem 0;
        }
        a {
            text-decoration: none;
            color: #0b5394;
            font-weight: bold;
        }
        a:hover,
        a:focus {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Meny</h1>
    <p>Velg en funksjon for å arbeide med klasser og studenter.</p>
    <ul>
        <li><a href="classes.php">Vis klasser</a></li>
        <li><a href="add_class.php">Legg til klasse</a></li>
        <li><a href="students.php">Vis studenter</a></li>
        <li><a href="add_student.php">Legg til student</a></li>
    </ul>
</body>
</html>
