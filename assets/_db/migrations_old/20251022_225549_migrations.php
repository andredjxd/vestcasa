<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'migrations', [
        'id' => "int(11) NOT NULL",
        'nome' => "varchar(255) NOT NULL",
        'executado_em' => "datetime DEFAULT NULL",
    ]);
};
?>