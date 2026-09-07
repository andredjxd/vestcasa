<?php

require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_avarias_relatorio_fotos', [

        // ==========================================
        // CHAVE PRIMÁRIA
        // ==========================================

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        // ==========================================
        // RELAÇÃO ITEM
        // ==========================================

        'item_id' => "int(11) NULL",

        'codigo_avaria' => "varchar(50) NULL",

        'codigo_barras' => "varchar(50) NULL",

        // ==========================================
        // FOTO
        // ==========================================

        'nome_arquivo' => "varchar(255) NULL",

        'caminho_arquivo' => "varchar(500) NULL",

        'url_arquivo' => "varchar(500) NULL",

        'tipo_arquivo' => "varchar(50) NULL",

        'tamanho_arquivo' => "bigint NULL",

        'thumb_arquivo' => "varchar(500) NULL",

        // ==========================================
        // CONTROLE
        // ==========================================

        'created_at' =>
            "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        // ==========================================
        // ÍNDICES
        // ==========================================

        'INDEX:idx_item_id' =>
            "(item_id)",

        'INDEX:idx_codigo_avaria' =>
            "(codigo_avaria)",

        'INDEX:idx_codigo_barras' =>
            "(codigo_barras)",

    ]);

};