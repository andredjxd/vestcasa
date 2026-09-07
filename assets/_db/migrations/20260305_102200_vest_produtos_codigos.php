<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_produtos_codigos', [

        // ==============================
        // CHAVE PRIMÁRIA
        // ==============================

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        // ==============================
        // RELAÇÃO
        // ==============================

        'sku' => "varchar(50) NOT NULL",
        'codigo_barras' => "varchar(50) NOT NULL",

        // ==============================
        // CONTROLE
        // ==============================

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        // ==============================
        // ÍNDICES
        // ==============================

        'UNIQUE:codigo_barras' => "(codigo_barras)", // garante que não repita
        'INDEX:idx_sku' => "(sku)",

    ]);

};