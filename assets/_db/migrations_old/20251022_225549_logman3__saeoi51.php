<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__saeoi51', [
        'evento' => "int(4) NOT NULL",
        'codigo' => "int(10) NOT NULL",
        'sub' => "int(3) NOT NULL",
        'descricao' => "varchar(100) NOT NULL",
        'emb' => "varchar(100) NOT NULL",
        'emb1' => "int(10) NOT NULL",
        'emb9' => "int(10) NOT NULL",
        'valor' => "decimal(10,2) NOT NULL",
        'dataeve' => "varchar(10) NOT NULL",
        'operacao' => "varchar(100) NOT NULL",
        'setorNumero' => "int(3) NOT NULL",
        'setorDescricao' => "varchar(100) NOT NULL",
        'grupo' => "varchar(100) NOT NULL",
    ]);
};
?>