<?php
require '../config.php';

print_r("START\n");

$pdo = new PDO(
    "pgsql:host=" . $configDb['host'] . ";dbname=" . $configDb['dbname'] . ";port=" . $configDb['port'],
    $configDb['user'],
    $configDb['pass']
);
// $pdo->exec("SET NAMES 'utf8';");

print_r("LOAD XML\n");
$data = simplexml_load_file('http://www.cbr.ru/scripts/XML_daily.asp');
if (empty($data)) {
    throw new Exception('empty XML from URL');
}

$insertData = [];
foreach ($data->Valute as $valute) {
    $insertData[] = sprintf("('%s', '%s', %0.4f)",
        $valute->attributes()->__toString(),
        $valute->Name->__toString(),
        str_replace(',', '.', $valute->Value->__toString()) / $valute->Nominal->__toString()
    );
}
$sqlInsert = sprintf('INSERT INTO main.currency (id, name, rate) VALUES %s', implode(',', $insertData));

print_r("EXEC SQL\n");
try {
    $pdo->beginTransaction();
    $pdo->exec("DELETE FROM main.currency");
    $pdo->exec($sqlInsert);
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Ошибка: " . $e->getMessage();
}

print_r('END');
