<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__estorno_valor', [
        'id' => "int(11) NOT NULL",
        'filial' => "int(5) NOT NULL",
        'data' => "date NOT NULL",
        'nrped' => "int(11) NOT NULL",
        'operador' => "int(11) NOT NULL",
        'motiv' => "varchar(100) NOT NULL",
        'codigo' => "int(11) NOT NULL",
        'descri' => "varchar(100) NOT NULL",
        'embala' => "varchar(50) NOT NULL",
        'qtdade' => "int(11) NOT NULL",
        'prund' => "int(11) NOT NULL",
        'prtotal' => "double(10,2) NOT NULL",
        'prest' => "double(10,2) NOT NULL",
        'difund' => "double(10,2) NOT NULL",
        'diftotal' => "double(10,2) NOT NULL",
    ]);
};
?>