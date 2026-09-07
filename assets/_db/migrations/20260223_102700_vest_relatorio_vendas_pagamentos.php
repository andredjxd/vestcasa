<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_relatorio_vendas_pagamentos', [

        // ==============================
        // CHAVE PRIMÁRIA
        // ==============================

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        // ==============================
        // IDENTIFICAÇÃO DA NOTA
        // ==============================

        'identificador' => "varchar(50) NOT NULL",
        'serie_nf'      => "int(11) NOT NULL",
        'nNF'           => "int(11) NOT NULL",

        // ==============================
        // IDENTIFICAÇÃO NF
        // ==============================

        'identificadornf' => "varchar(100) NULL",

        // ==============================
        // PAGAMENTO
        // ==============================
        // 01 - DINHEIRO
        // 02 -
        // 03 - CREDITO
        // 04 - DEBITO
        // 99 - VOUCHER

        'tPag'      => "varchar(5) NOT NULL",
        'vPag'      => "decimal(10,2) NOT NULL",
        'tpIntegra' => "int(11) NULL",

        // ==============================
        // CONTROLE
        // ==============================

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        // ==============================
        // ÍNDICES (EXATOS DO BANCO)
        // ==============================

        'INDEX:idx_identificador' => "(identificador)",

        'INDEX:idx_nf' => "(serie_nf, nNF)",

    ]);

};