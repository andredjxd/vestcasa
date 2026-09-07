<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__sexoi23', [
        'codigo' => "int(11) NOT NULL",
        'sub_codigo' => "int(3) NOT NULL",
        'filial' => "int(3) NOT NULL",
        'filial_dest' => "int(3) NOT NULL",
        'filial_dest_nome' => "varchar(50) NOT NULL",
        'setor' => "int(3) NOT NULL",
        'codigo_mercad' => "int(11) NOT NULL",
        'descricao' => "varchar(100) NOT NULL",
        'emb' => "varchar(100) NOT NULL",
        'comprador' => "varchar(100) NOT NULL",
        'lastro' => "int(11) NOT NULL",
        'ideal' => "int(11) NOT NULL",
        'peso' => "float(10,2) NOT NULL",
        'data' => "date NOT NULL",
        'aut' => "int(11) NOT NULL",
        'qtdpal' => "float(10,2) NOT NULL",
        'qtd' => "int(11) NOT NULL",
        'valor_total' => "double(10,2) NOT NULL",
    ]);
};
?>