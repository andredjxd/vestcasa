<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__operadores', [
        'id' => "int(11) NOT NULL",
        'operador' => "int(11) NOT NULL",
        'nome' => "varchar(100) NOT NULL",
        'login' => "varchar(100) NOT NULL",
        'data' => "date NOT NULL",
        'hora' => "time NOT NULL",
        'status' => "int(11) NOT NULL",
    ]);
};
?>