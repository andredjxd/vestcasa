<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__promotor_filtro', [
        'id' => "int(10) NOT NULL",
        'empresa' => "varchar(100) NOT NULL",
        'promotor' => "varchar(100) NOT NULL",
        'filtro' => "varchar(500) NOT NULL",
        'dias_semana' => "varchar(20) NOT NULL",
        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
        'updated_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    ]);
};
?>