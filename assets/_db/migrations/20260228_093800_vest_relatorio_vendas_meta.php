<?php
// Arquivo gerado automaticamente
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'vest_relatorio_vendas_meta', [

        'id'            => "int(11) unsigned NOT NULL AUTO_INCREMENT",

        // Data da meta (1 meta por dia)
        'data'          => "date NOT NULL",

        // Percentual taxa administrativa
        'taxa_adm'      => "decimal(10,2) NOT NULL DEFAULT 0.00",

        // Despesas
        'desp_pessoal'  => "decimal(10,2) NOT NULL DEFAULT 0.00",
        'desp_geral'    => "decimal(10,2) NOT NULL DEFAULT 0.00",

        // Metas
        'venda_meta'    => "decimal(10,2) NOT NULL DEFAULT 0.00",
        'rent_meta'     => "decimal(10,2) NOT NULL DEFAULT 0.00",

        // Controle
        'created'       => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",

        // ==============================
        // ÍNDICES
        // ==============================

        // Evita duas metas no mesmo dia
        'UNIQUE:uniq_data' => "(data)",

        'INDEX:idx_data'   => "(data)",

    ]);
};