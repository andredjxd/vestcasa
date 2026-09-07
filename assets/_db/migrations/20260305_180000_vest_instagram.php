<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_instagram', [

        // ==============================
        // CHAVE PRIMÁRIA
        // ==============================

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        // ==============================
        // DADOS INSTAGRAM
        // ==============================

        'data' => "date NOT NULL",
        'seguidores' => "int(11) NOT NULL DEFAULT 0",
        'reels' => "int(11) NOT NULL DEFAULT 0",
        'story' => "int(11) NOT NULL DEFAULT 0",
        'live' => "int(11) NOT NULL DEFAULT 0",

        // ==============================
        // CONTROLE
        // ==============================

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        // ==============================
        // ÍNDICES
        // ==============================

        'UNIQUE:data' => "(data)",
        'INDEX:idx_data' => "(data)",

    ]);

};