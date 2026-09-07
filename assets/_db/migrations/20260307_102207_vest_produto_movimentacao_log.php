<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_produto_movimentacao_log', [

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        'sku' => "varchar(50) NOT NULL",

        'tipo' => "varchar(50) NULL",

        'documento' => "varchar(50) NULL",
        
        'identificador' => "varchar(100) NULL",

        'usuario' => "varchar(100) NULL",

        'descricao' => "text NULL",

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        'INDEX:idx_sku' => "(sku)"

    ]);
};