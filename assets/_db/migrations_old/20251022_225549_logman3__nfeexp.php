<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__nfeexp', [
        'id' => "int(11) NOT NULL",
        'data' => "date NOT NULL",
        'nfe' => "int(11) NOT NULL",
        'natOp' => "varchar(50) NOT NULL",
        'qItens' => "int(11) NOT NULL",
        'vNota' => "double(10,2) NOT NULL",
        'filial_dest_cnpj' => "varchar(50) NOT NULL",
        'filial_emit_cnpj' => "varchar(50) NOT NULL",
    ]);
};
?>