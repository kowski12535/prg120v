# PRG120V PHP webapplikasjon

En enkel PHP/MySQL-applikasjon som lar deg registrere, vise og slette klasser og studenter via en meny (`index.php`).

## Struktur

- `config.php` oppretter en PDO-tilkobling basert på miljøvariabler.
- `index.php` viser menyen.
- `classes.php` viser alle klasser og lar deg slette én.
- `add_class.php` lar deg registrere en ny klasse.
- `students.php` viser alle studenter og lar deg slette én.
- `add_student.php` lar deg registrere en ny student med valg av klasse.

## Konfigurasjon

Sett følgende miljøvariabler (Dokploy støtter dette direkte):

```bash
DB_HOST=<database-vert>
DB_NAME=<database-navn>
DB_USER=<database-bruker>
DB_PASS=<database-passord>
```

Standardverdier: `localhost`, `prg120v`, `root`, tomt passord.

## Database

Eksempel på SQL-script (`database.sql`) du kan kjøre mot databasen:

```sql
CREATE TABLE KLASSE (
    klassekode VARCHAR(10) PRIMARY KEY,
    klassenavn VARCHAR(100) NOT NULL,
    studiumkode VARCHAR(10) NOT NULL
);

CREATE TABLE STUDENT (
    brukernavn VARCHAR(20) PRIMARY KEY,
    fornavn VARCHAR(50) NOT NULL,
    etternavn VARCHAR(50) NOT NULL,
    klassekode VARCHAR(10) NOT NULL,
    CONSTRAINT fk_klasse FOREIGN KEY (klassekode) REFERENCES KLASSE (klassekode)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

INSERT INTO KLASSE (klassekode, klassenavn, studiumkode) VALUES
('IT1', 'IT og ledelse 1. år', 'ITLED'),
('IT2', 'IT og ledelse 2. år', 'ITLED'),
('IT3', 'IT og ledelse 3. år', 'ITLED');

INSERT INTO STUDENT (brukernavn, fornavn, etternavn, klassekode) VALUES
('gb', 'Geir', 'Bjarvin', 'IT1'),
('mrj', 'Marius R.', 'Johannessen', 'IT1'),
('tb', 'Tove', 'Bøe', 'IT2');
```

## Kjøre lokalt

1. Sørg for at PHP (>=8.0) og MySQL er tilgjengelig.
2. Opprett databasen og importer SQL-scriptet over.
3. Start PHPs innebygde server fra prosjektmappen:
   ```bash
   php -S localhost:8000
   ```
4. Åpne `http://localhost:8000/index.php`.

## Deploy med Dokploy

1. Opprett en app i Dokploy med PHP som runtime.
2. Sett miljøvariablene for databasen.
3. Pek appen mot repoet og deploy. Applikasjonen vil være tilgjengelig på URL-en Dokploy oppgir, f.eks. `https://dokploy.usn.no/app/BRUKERNAVN-REPONAVN`.
