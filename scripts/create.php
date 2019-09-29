<?php
require '../config.php';

$pdo = new PDO(
    "pgsql:host=" . $configDb['host'] . ";dbname=" . $configDb['dbname'] . ";port=" . $configDb['port'],
    $configDb['user'],
    $configDb['pass']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$pdo->exec("CREATE TABLE IF NOT EXISTS main.currency
    (
        id varchar UNIQUE not null CONSTRAINT firstkey PRIMARY KEY,
        name varchar not null,
        rate numeric not null
    )
");

$hash = password_hash('admin', PASSWORD_DEFAULT);
$pdo->exec("CREATE TABLE IF NOT EXISTS main.users
        (
            login varchar UNIQUE not null,
            hash varchar not null
        );
    INSERT INTO main.users (login, hash) VALUES ('admin', '$hash');
");

$pdo->exec("CREATE TABLE IF NOT EXISTS main.tokens
    (
        token varchar UNIQUE not null,
        decay timestamp not null
    )
");
