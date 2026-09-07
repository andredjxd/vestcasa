<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__terceiros', [
        'id' => "int(11) NOT NULL",
        'data' => "date NOT NULL",
        'cpf' => "varchar(15) NOT NULL",
        'nome' => "varchar(200) NOT NULL",
        'tipo' => "varchar(50) NOT NULL",
        'empresa' => "varchar(200) NOT NULL",
        'codaut' => "int(11) NOT NULL",
        'filial' => "int(4) NOT NULL",
    ]);
};
?>