<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_relatorio_vendas_itens', [

        // ==============================
        // CHAVE PRIMÁRIA
        // ==============================

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        // ==============================
        // IDENTIFICAÇÃO DO ITEM
        // ==============================

        'num_item'      => "int(11) NULL",
        'identificador' => "varchar(100) NULL",
        'serie_nf'      => "int(11) NULL",
        'num_nf'        => "int(11) NULL",

        // ==============================
        // IDENTIFICAÇÃO NF
        // ==============================

        'identificadornf' => "varchar(100) NULL",

        // ==============================
        // DADOS DO PRODUTO
        // ==============================

        'codigo_produto' => "varchar(50) NULL",
        'descricao'      => "varchar(255) NULL",
        'quantidade'     => "decimal(10,3) NULL",
        'valor_unitario' => "decimal(10,2) NULL",
        'valor_total'    => "decimal(10,2) NULL",

        // ==============================
        // CONTROLE
        // ==============================

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        // ==============================
        // ÍNDICES (EXATAMENTE COMO ESTÁ)
        // ==============================

        // UNIQUE identificador + num_item
        'UNIQUE:uk_nf_item' => "(identificador, num_item)",

        // INDEX simples
        'INDEX:idx_identificador' => "(identificador)",

        // INDEX composto serie + numero
        'INDEX:idx_serie_nf' => "(serie_nf, num_nf)",

        'INDEX:idx_produto_nf' => "(codigo_produto, identificadornf)",

    ]);

};