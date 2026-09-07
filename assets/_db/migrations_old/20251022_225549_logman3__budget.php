<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__budget', [
        'id' => "int(11) NOT NULL",
        'rol' => "varchar(255) NOT NULL",
        'nome' => "varchar(200) DEFAULT NULL",
        'data' => "date NOT NULL",
        'realizadoanterior' => "decimal(10,2) DEFAULT NULL",
        'previsto' => "decimal(10,2) DEFAULT NULL",
        'realizado' => "decimal(10,2) DEFAULT NULL",
    ]);
};
?>