<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_produto_estoque', [

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        'sku' => "varchar(50) NOT NULL",

        'quantidade' => "int(11) DEFAULT 0",

        'reservado' => "int(11) DEFAULT 0",

        'estoque_minimo' => "int(11) DEFAULT 0",

        'updated_at' => "timestamp NULL",

        'UNIQUE:sku' => "(sku)"

    ]);
};