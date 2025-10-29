CREATE DATABASE IF NOT EXISTS fadac3356 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fadac3356;

CREATE TABLE IF NOT EXISTS KLASSE (
    klassekode VARCHAR(10) PRIMARY KEY,
    klassenavn VARCHAR(100) NOT NULL,
    studiumkode VARCHAR(10) NOT NULL
);

CREATE TABLE IF NOT EXISTS STUDENT (
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
('IT3', 'IT og ledelse 3. år', 'ITLED')
ON DUPLICATE KEY UPDATE
klassenavn = VALUES(klassenavn),
studiumkode = VALUES(studiumkode);

INSERT INTO STUDENT (brukernavn, fornavn, etternavn, klassekode) VALUES
('gb', 'Geir', 'Bjarvin', 'IT1'),
('mrj', 'Marius R.', 'Johannessen', 'IT1'),
('tb', 'Tove', 'Bøe', 'IT2')
ON DUPLICATE KEY UPDATE
fornavn = VALUES(fornavn),
etternavn = VALUES(etternavn),
klassekode = VALUES(klassekode);
