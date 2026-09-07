<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__saeoi030_av', [
        'grupo' => "int(2) NOT NULL",
        'descr_grupo' => "varchar(100) NOT NULL",
        'valor' => "decimal(10,2) NOT NULL",
        'projecao' => "decimal(10,2) NOT NULL",
        'venda' => "decimal(10,2) NOT NULL",
        'percentual' => "decimal(10,2) NOT NULL",
    ]);
};
?>