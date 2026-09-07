<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__smgoi13', [
        'data' => "date NOT NULL",
        'codigo' => "int(7) DEFAULT NULL",
        'sub_cod' => "int(3) DEFAULT NULL",
        'descricao' => "varchar(100) DEFAULT NULL",
        'emb' => "varchar(100) DEFAULT NULL",
        'setor_numero' => "int(3) DEFAULT NULL",
        'setor_loja' => "varchar(100) DEFAULT NULL",
        'comprador' => "varchar(200) NOT NULL",
        'dt_ult_entr' => "varchar(10) DEFAULT NULL",
        'qtd_ult_entr' => "int(10) DEFAULT NULL",
        'est_emb1' => "int(10) DEFAULT NULL",
        'est_emb9' => "int(10) DEFAULT NULL",
        'custo_medio' => "decimal(10,2) NOT NULL",
        'preco_venda' => "decimal(10,2) NOT NULL",
        'rent' => "decimal(10,2) NOT NULL",
        'total_estoque' => "decimal(10,2) NOT NULL",
        'qtd_venda_emb1' => "int(10) DEFAULT NULL",
        'idade' => "decimal(10,2) NOT NULL",
        'nao_vende' => "int(11) DEFAULT NULL",
        'preco_venda_und' => "double(10,2) NOT NULL",
        'gramatura' => "varchar(50) NOT NULL",
        'venda' => "varchar(10) NOT NULL",
        'dtvnd' => "date DEFAULT NULL",
    ]);
};
?>