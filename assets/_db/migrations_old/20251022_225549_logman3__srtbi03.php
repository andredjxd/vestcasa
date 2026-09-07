<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__srtbi03', [
        'data' => "date NOT NULL",
        'codigo' => "int(10) NOT NULL",
        'sub' => "int(3) NOT NULL",
        'descricao' => "varchar(100) NOT NULL",
        'emb' => "varchar(100) NOT NULL",
        'emb1' => "int(10) NOT NULL",
        'emb9' => "int(10) NOT NULL",
        'preco_medio' => "double(10,2) NOT NULL",
        'prazo' => "double(10,2) NOT NULL",
        'valor_venda' => "double(10,2) NOT NULL",
        'variacao' => "double(10,2) NOT NULL",
        'rent' => "double(10,2) NOT NULL",
        'provisao' => "double(10,2) NOT NULL",
        'avaria' => "double(10,2) NOT NULL",
        'ajuste' => "double(10,2) NOT NULL",
        'resultado' => "double(10,2) NOT NULL",
        'tipo_analise' => "varchar(100) NOT NULL",
        'resul_ll' => "double(10,2) NOT NULL",
    ]);
};
?>