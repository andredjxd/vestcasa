<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__setor', [
        'id' => "int(11) NOT NULL",
        'setor' => "varchar(50) NOT NULL",
        'filtro_setores' => "varchar(200) NOT NULL",
        'meta' => "double(10,2) NOT NULL",
    ]);
};
?>