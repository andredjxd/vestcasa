<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__validade_relatorio_item', [
        'id' => "int(11) NOT NULL",
        'id_rel' => "int(11) NOT NULL",
        'codigo' => "int(11) NOT NULL",
        'sub' => "int(3) NOT NULL",
        'descricao' => "varchar(100) NOT NULL",
        'emb' => "varchar(100) DEFAULT NULL",
        'comprador' => "varchar(100) DEFAULT NULL",
        'data' => "date DEFAULT NULL",
        'rentrea' => "double(10,2) NOT NULL",
        'vndreal' => "varchar(100) DEFAULT NULL",
        'rentatu' => "double(10,2) NOT NULL",
        'vnd30ds' => "int(11) NOT NULL",
        'estoque' => "varchar(100) NOT NULL",
        'cxa' => "int(11) NOT NULL",
        'und' => "int(11) NOT NULL",
        'resultado' => "double(10,2) NOT NULL",
        'datacreate' => "date NOT NULL",
        'horacreate' => "time NOT NULL",
        'dataupdate' => "date NOT NULL",
        'horaupdate' => "time NOT NULL",
    ]);
};
?>