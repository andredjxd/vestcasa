<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__conferencia_itens', [
        'id' => "int(11) NOT NULL",
        'id_nota' => "int(11) NOT NULL",
        'nota' => "int(11) NOT NULL",
        'seq' => "int(3) NOT NULL",
        'codigo' => "int(11) NOT NULL",
        'codigo_barra' => "varchar(50) NOT NULL",
        'descricao' => "varchar(100) NOT NULL",
        'emb' => "varchar(100) DEFAULT NULL",
        'emb1' => "varchar(10) NOT NULL",
        'qtdc' => "float(10,2) NOT NULL",
        'precoc' => "double(10,2) NOT NULL",
        'preco_total' => "double(10,2) NOT NULL",
        'emb9' => "varchar(10) NOT NULL",
        'qtdu' => "float(10,2) NOT NULL",
        'precou' => "double(10,2) NOT NULL",
    ]);
};
?>