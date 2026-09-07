<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__conferencia_nota', [
        'id' => "int(11) NOT NULL",
        'nota' => "int(11) NOT NULL",
        'seq' => "int(3) NOT NULL",
        'data' => "date NOT NULL",
        'hora' => "time NOT NULL",
        'cnpj' => "varchar(15) NOT NULL",
        'nome' => "varchar(100) NOT NULL",
        'valor' => "double(10,2) NOT NULL",
        'status' => "int(2) NOT NULL",
    ]);
};
?>