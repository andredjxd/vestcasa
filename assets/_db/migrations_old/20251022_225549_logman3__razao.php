<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__razao', [
        'id' => "int(11) NOT NULL",
        'filial' => "varchar(20) NOT NULL",
        'conta' => "varchar(20) NOT NULL",
        'rol' => "varchar(20) NOT NULL",
        'descricao' => "varchar(255) DEFAULT NULL",
        'data_movimento' => "date NOT NULL",
        'lote' => "varchar(20) NOT NULL",
        'sequencia' => "varchar(20) NOT NULL",
        'acerto' => "varchar(20) DEFAULT NULL",
        'class' => "varchar(20) DEFAULT NULL",
        'historico' => "text DEFAULT NULL",
        'valor' => "decimal(15,2) NOT NULL",
        'documento' => "varchar(50) NOT NULL",
        'complemento' => "varchar(255) DEFAULT NULL",
        'parceiro' => "varchar(255) DEFAULT NULL",
    ]);
};
?>