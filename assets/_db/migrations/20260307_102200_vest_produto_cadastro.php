<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_produto_cadastro', [

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        'sku' => "varchar(50) NOT NULL",
        'produto' => "varchar(255) NULL",
        'marca' => "varchar(150) NULL",
        'cod_fornecedor' => "varchar(150) NULL",
        'status' => "int(11) NOT NULL DEFAULT 0",

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        'UNIQUE:sku' => "(sku)",
        'INDEX:idx_sku' => "(sku)",

    ]);
};