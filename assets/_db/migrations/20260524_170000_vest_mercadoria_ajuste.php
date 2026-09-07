<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_mercadoria_ajuste', [

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        'codigo_barras' => "varchar(50) NOT NULL",

        'sku' => "varchar(50) NOT NULL",

        'produto' => "varchar(255) NULL",

        'marca' => "varchar(150) NULL",

        'quantidade_ajuste' => "int(11) NOT NULL",

        'estoque_anterior' => "int(11) NULL",

        'estoque_posterior' => "int(11) NULL",

        'status' => "int(3) NOT NULL DEFAULT 1",

        'usuario_criacao' => "varchar(100) NULL",

        'usuario_ajuste' => "varchar(100) NULL",

        'observacao' => "varchar(255) NULL",

        'data_ajuste' => "datetime NULL",

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        'updated_at' => "timestamp NULL",

        'INDEX:idx_status' => "(status)",
        'INDEX:idx_sku_status' => "(sku, status)",
        'INDEX:idx_codigo_barras' => "(codigo_barras)",
        'INDEX:idx_created_at' => "(created_at)"

    ]);
};
