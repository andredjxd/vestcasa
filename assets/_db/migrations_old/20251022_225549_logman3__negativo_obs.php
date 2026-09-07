<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__negativo_obs', [
        'id' => "int(11) NOT NULL",
        'codigo' => "int(11) NOT NULL",
        'codigokill' => "int(11) DEFAULT NULL",
        'obs' => "varchar(300) NOT NULL",
        'time' => "timestamp NOT NULL",
    ]);
};
?>