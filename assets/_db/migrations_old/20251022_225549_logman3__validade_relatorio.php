<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__validade_relatorio', [
        'id' => "int(11) NOT NULL",
        'setor' => "varchar(100) NOT NULL",
        'comprador' => "varchar(100) NOT NULL",
        'nome' => "varchar(100) NOT NULL",
        'data' => "date NOT NULL",
        'hora' => "time NOT NULL",
        'status' => "int(11) NOT NULL",
        'updatedata' => "date DEFAULT NULL",
    ]);
};
?>