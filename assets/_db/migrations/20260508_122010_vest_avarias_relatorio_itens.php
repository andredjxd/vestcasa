<?php

require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_avarias_relatorio_itens', [

        // ==========================================
        // CHAVE PRIMÁRIA
        // ==========================================

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        // ==========================================
        // RELAÇÃO AVARIA
        // ==========================================

        'codigo_avaria' => "varchar(50) NULL",

        // ==========================================
        // PRODUTO
        // ==========================================

        'codigo_barras' => "varchar(50) NULL",
        'produto'       => "varchar(500) NULL",
        'artigo'        => "varchar(255) NULL",

        // ==========================================
        // AVARIA
        // ==========================================

        'descricao_avaria' => "varchar(255) NULL",
        'tipo_avaria'      => "varchar(255) NULL",

        // ==========================================
        // CONTROLE
        // ==========================================

        'volume'       => "varchar(100) NULL",
        'agrupamento'  => "varchar(255) NULL",
        'quantidade'   => "decimal(10,2) NULL",

        // ==========================================
        // FINANCEIRO
        // ==========================================

        'preco_contrapartida' => "decimal(10,2) NULL",

        // ==========================================
        // OBSERVAÇÃO
        // ==========================================

        'observacao' => "text NULL",

        // ==========================================
        // TIMESTAMP
        // ==========================================

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
        'updated_at' => "timestamp NULL DEFAULT NULL",

        // ==========================================
        // ÍNDICES
        // ==========================================

        'INDEX:idx_codigo_avaria' => "(codigo_avaria)",

        'INDEX:idx_codigo_barras' => "(codigo_barras)",

        'INDEX:idx_artigo' => "(artigo)",

        'INDEX:idx_tipo_avaria' => "(tipo_avaria)",

        // evita duplicidade do mesmo item
        'UNIQUE:uniq_avaria_produto_tipo' =>
            "(codigo_avaria, codigo_barras, tipo_avaria)"
    ]);

};