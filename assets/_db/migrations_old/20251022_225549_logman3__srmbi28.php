<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__srmbi28', [
        'setor_loja_num' => "int(3) NOT NULL",
        'setor_loja_nom' => "varchar(50) NOT NULL",
        'codigo' => "int(7) NOT NULL",
        'sub_codigo' => "int(3) NOT NULL",
        'descricao' => "varchar(50) NOT NULL",
        'emb' => "varchar(50) NOT NULL",
        'est1' => "int(20) NOT NULL",
        'est9' => "int(20) NOT NULL",
        'ent1' => "int(20) NOT NULL",
        'ent9' => "int(20) NOT NULL",
        'tot1' => "int(20) NOT NULL",
        'tot9' => "int(20) NOT NULL",
        'dt_ult_vnd' => "varchar(10) NOT NULL",
        'dt_referencia' => "varchar(10) NOT NULL",
        'vendeu' => "varchar(3) NOT NULL",
    ]);
};
?>