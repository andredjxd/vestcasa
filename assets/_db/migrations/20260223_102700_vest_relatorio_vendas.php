<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_relatorio_vendas', [

        // ==============================
        // CHAVE PRIMÁRIA
        // ==============================

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        // ==============================
        // IDENTIFICAÇÃO
        // ==============================

        'identificador' => "varchar(100) NULL",

        // ==============================
        // STATUS
        // ==============================

        'status_nf' => "varchar(10) NULL",
        'loja'      => "varchar(150) NULL",
        'pdv'       => "int(11) NULL",

        // ==============================
        // DADOS DA VENDA
        // ==============================

        'data_hora' => "datetime NULL",
        'tipo_nota' => "varchar(20) NULL",
        'num_nf'    => "int(11) NULL",
        'serie_nf'  => "int(11) NULL",
        'total'     => "decimal(10,2) NULL",

        // ==============================
        // CONTROLE
        // ==============================

        'ultimo_status' => "varchar(255) NULL",
        'created_at'    => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        // ==============================
        // ÍNDICES OTIMIZADOS
        // ==============================

        // 🔥 NOVO (ESSENCIAL)
        'INDEX:idx_status_data' => "(status_nf, data_hora)",

        // 👍 manter se usar em outras consultas
        'INDEX:idx_identificador' => "(identificador)",

        'INDEX:idx_identificador_data' => "(identificador, data_hora)",

        // INDEXES
        // 'INDEX:idx_identificador' => "(identificador)",
        // 'INDEX:idx_status_nf'     => "(status_nf)",
        // 'INDEX:idx_data_hora'     => "(data_hora)",

    ]);

};