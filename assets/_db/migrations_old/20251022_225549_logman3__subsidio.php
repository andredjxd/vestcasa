<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__subsidio', [
        'id' => "int(11) NOT NULL",
        'dataadd' => "date NOT NULL",
        'filial' => "int(11) NOT NULL",
        'comprador' => "int(11) NOT NULL",
        'codigo' => "int(11) NOT NULL",
        'descricao' => "varchar(50) NOT NULL",
        'emb' => "varchar(50) NOT NULL",
        'cxa' => "int(11) NOT NULL",
        'und' => "int(11) NOT NULL",
        'promocao' => "varchar(50) NOT NULL",
        'vlr_und' => "double(10,2) NOT NULL",
        'qtd_und' => "int(11) NOT NULL",
        'validade' => "date NOT NULL",
        'vlr_total' => "double(10,2) NOT NULL",
        'obs' => "varchar(100) NOT NULL",
    ]);
};
?>