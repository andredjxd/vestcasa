<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_rme_recebimento', [

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        'loja' => "varchar(150) NULL",
        'cnpj' => "varchar(20) NULL",

        'pedido' => "varchar(20) NULL",
        'nota' => "varchar(20) NULL",
        'chamado' => "varchar(20) NULL",

        'data_faturamento' => "date NULL",
        'data_recebimento' => "date NULL",
        'data_fechamento' => "date NULL",

        // 0 aguardando
        // 1 conferindo
        // 2 conferido
        // 3 finalizado

        'status' => "int(1) DEFAULT 0",

        'usuario_id'   => "int(11) NULL",
        'usuario_nome' => "varchar(150) NULL",

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        'UNIQUE:uniq_recebimento' => "(pedido,nota)",

        'INDEX:idx_nota' => "(nota)",
        'INDEX:idx_pedido' => "(pedido)"

    ]);
};