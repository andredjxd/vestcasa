<?php

require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_avarias_relatorio', [

        // ==========================================
        // CHAVE PRIMÁRIA
        // ==========================================

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        // ==========================================
        // DADOS AVARIA
        // ==========================================

        'codigo_avaria'      => "varchar(50) NULL",
        'situacao'           => "varchar(100) NULL",
        'criado_por'         => "varchar(255) NULL",
        'data_criacao'       => "datetime NULL",
        'descricao'          => "varchar(255) NULL",
        'empresa'            => "varchar(255) NULL",
        'deposito'           => "varchar(255) NULL",
        'quantidade_produto' => "int(11) NULL",

        // ==========================================
        // CONTROLE
        // ==========================================

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
        'updated_at' => "timestamp NULL DEFAULT NULL",

        // ==========================================
        // ÍNDICES
        // ==========================================

        'UNIQUE:uniq_codigo_avaria' => "(codigo_avaria)",

        'INDEX:idx_situacao' => "(situacao)",

        'INDEX:idx_data_criacao' => "(data_criacao)",

        'INDEX:idx_empresa' => "(empresa)",

        'INDEX:idx_deposito' => "(deposito)",

    ]);

};