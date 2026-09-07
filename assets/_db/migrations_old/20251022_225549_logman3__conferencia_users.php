<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__conferencia_users', [
        'id' => "int(11) NOT NULL",
        'nome' => "varchar(100) NOT NULL",
        'email' => "varchar(100) NOT NULL",
        'pass' => "varchar(200) NOT NULL",
    ]);
};
?>