<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__corredores', [
        'id' => "int(10) NOT NULL",
        'setor' => "varchar(50) NOT NULL",
        'corredor' => "varchar(100) NOT NULL",
        'setores_filtro' => "varchar(100) NOT NULL",
        'corredor_numero' => "varchar(50) NOT NULL",
        'meta' => "double(10,2) NOT NULL",
    ]);
};
?>