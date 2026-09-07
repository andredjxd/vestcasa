<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_produto_extrato', [

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        'sku' => "varchar(50) NOT NULL",

        // ENTRADA
        // SAIDA
        // AJUSTE
        // TRANSFERENCIA
        // DEVOLUCAO

        'tipo_movimento' => "varchar(20) NOT NULL",

        'documento' => "varchar(50) NULL",
        
        'identificador' => "varchar(100) NULL",

        'qtde' => "int(11) NOT NULL",

        'saldo' => "int(11) NULL",

        'usuario' => "varchar(100) NULL",

        'data_recebimento' => "date NULL",

        'data_movimento' => "datetime NULL",

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        'INDEX:idx_sku' => "(sku)",

        'INDEX:idx_sku_data' => "(sku,data_movimento)"

    ]);
};