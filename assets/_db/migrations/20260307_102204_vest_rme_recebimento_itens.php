<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_rme_recebimento_itens', [

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        'recebimento_id' => "int(11) NOT NULL",

        'sku' => "varchar(50) NOT NULL",

        'pack' => "int(11) NULL",

        'qtde_esperada' => "int(11) NULL",
        'qtde_conferida' => "int(11) NULL",

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        'UNIQUE:uniq_recebimento_sku' => "(recebimento_id,sku)",

        'INDEX:idx_recebimento' => "(recebimento_id)",
        'INDEX:idx_sku' => "(sku)"

    ]);
};