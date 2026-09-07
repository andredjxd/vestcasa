<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__budget_vendas', [
        'id' => "int(11) NOT NULL",
        'tipo' => "varchar(255) NOT NULL",
        'data' => "date NOT NULL",
        'anterior' => "decimal(10,2) NOT NULL",
        'previsto' => "decimal(10,2) DEFAULT NULL",
        'realizado' => "decimal(10,2) DEFAULT NULL",
    ]);
};
?>