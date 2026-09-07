<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__smgoi11', [
        'codigo' => "varchar(11) DEFAULT NULL",
        'sub_codigo' => "int(11) DEFAULT NULL",
        'descricao' => "varchar(100) DEFAULT NULL",
        'emb' => "varchar(100) DEFAULT NULL",
        'qtd_ped_comp' => "int(11) DEFAULT NULL",
        'est_emb1' => "int(11) DEFAULT NULL",
        'est_emb9' => "int(11) DEFAULT NULL",
        'vnd30' => "int(11) DEFAULT NULL",
    ]);
};
?>