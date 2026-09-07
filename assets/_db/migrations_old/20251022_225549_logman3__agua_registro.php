<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__agua_registro', [
        'id' => "int(11) NOT NULL",
        'data' => "date NOT NULL",
        'leitura06' => "float(10,3) DEFAULT NULL",
        'nome1' => "varchar(100) DEFAULT NULL",
        'leitura23' => "float(10,3) DEFAULT NULL",
        'nome2' => "varchar(100) DEFAULT NULL",
        'update' => "datetime NOT NULL",
    ]);
};
?>