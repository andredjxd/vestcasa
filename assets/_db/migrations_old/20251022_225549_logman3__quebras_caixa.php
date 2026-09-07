<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__quebras_caixa', [
        'id' => "int(11) NOT NULL",
        'filial' => "varchar(50) NOT NULL",
        'dt_quebra' => "date NOT NULL",
        'tipo_quebra' => "varchar(50) NOT NULL",
        'operador' => "varchar(50) NOT NULL",
        'usuario' => "varchar(100) DEFAULT NULL",
        'valor_qb' => "decimal(10,2) NOT NULL",
        'valor_pg' => "decimal(10,2) NOT NULL",
        'resta_pg' => "decimal(10,2) NOT NULL",
        'status' => "int(11) NOT NULL",
        'created_at' => "timestamp NOT NULL",
    ]);
};
?>