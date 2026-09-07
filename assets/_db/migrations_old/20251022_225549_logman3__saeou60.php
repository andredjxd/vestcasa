<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__saeou60', [
        'date' => "date NOT NULL",
        'codigo' => "int(11) NOT NULL",
        'descricao' => "varchar(100) NOT NULL",
        'emb' => "varchar(100) NOT NULL",
        'est1' => "int(11) NOT NULL",
        'est9' => "int(11) NOT NULL",
        'data_venc' => "varchar(10) NOT NULL",
        'qt_ult_ent1' => "int(11) NOT NULL",
        'qt_ult_ent9' => "int(11) NOT NULL",
        'vnd30emb1' => "int(11) NOT NULL",
        'vnd30emb9' => "int(11) NOT NULL",
        'isv' => "int(11) NOT NULL",
        'setor_num' => "int(3) NOT NULL",
        'setor_nom' => "varchar(100) NOT NULL",
        'valor_venc' => "double(10,2) NOT NULL",
        'data_venc1' => "date NOT NULL",
    ]);
};
?>