<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__smgoi12', [
        'data' => "date DEFAULT NULL",
        'filial' => "int(4) NOT NULL",
        'codigo' => "varchar(11) DEFAULT NULL",
        'sub_codigo' => "int(11) DEFAULT NULL",
        'descricao' => "varchar(100) DEFAULT NULL",
        'emb' => "varchar(100) DEFAULT NULL",
        'cm' => "double(10,2) DEFAULT NULL",
        'qtd_ped_comp' => "int(11) DEFAULT NULL",
        'ped_filial' => "int(11) DEFAULT NULL",
        'ped_nf_at' => "int(11) DEFAULT NULL",
        'cod_comprador' => "int(4) DEFAULT NULL",
        'nome_comprador' => "varchar(200) DEFAULT NULL",
        'est_emb1' => "int(11) DEFAULT NULL",
        'est_emb9' => "int(11) DEFAULT NULL",
        'vnd30' => "int(11) DEFAULT NULL",
    ]);
};
?>