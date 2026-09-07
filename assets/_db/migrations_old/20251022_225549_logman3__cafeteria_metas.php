<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__cafeteria_metas', [
        'id' => "int(11) NOT NULL",
        'data' => "date NOT NULL",
        'taxa_adm' => "double(10,2) NOT NULL",
        'desp_pessoal' => "double(10,2) NOT NULL",
        'desp_geral' => "double(10,2) NOT NULL",
        'venda_meta' => "double(10,2) NOT NULL",
        'rent_meta' => "double(10,2) NOT NULL",
    ]);
};
?>