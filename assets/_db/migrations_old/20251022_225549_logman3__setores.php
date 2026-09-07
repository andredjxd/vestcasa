<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__setores', [
        'setor_num' => "int(3) NOT NULL",
        'setor_nom' => "varchar(100) NOT NULL",
    ]);
};
?>