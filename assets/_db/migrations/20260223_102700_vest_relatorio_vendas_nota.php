<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_relatorio_vendas_nota', [

        // ==============================
        // CHAVE PRIMÁRIA
        // ==============================

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        // ==============================
        // IDENTIFICAÇÃO
        // ==============================

        'identificador' => "varchar(50) NOT NULL",
        // ==============================
        // IDENTIFICAÇÃO NF
        // ==============================

        'identificadornf' => "varchar(100) NULL",

        // ==============================
        // DADOS DA NOTA
        // ==============================

        'natOp'        => "varchar(150) NULL",
        'modelo'       => "varchar(5) NULL",
        'serie_nf'     => "int(11) NULL",
        'nNF'          => "int(11) NULL",
        'dhEmi'        => "datetime NULL",
        'tpNF'         => "int(11) NULL",

        // ==============================
        // CLIENTE
        // ==============================

        'cpf_cliente'  => "varchar(14) NULL",
        'nome_cliente' => "varchar(150) NULL",

        // ==============================
        // VALORES
        // ==============================

        'vOutro' => "decimal(10,2) NULL",
        'vNF'    => "decimal(10,2) NULL",
        'vTroco'    => "decimal(10,2) NULL",

        // ==============================
        // INFORMAÇÕES ADICIONAIS
        // ==============================

        'infAdFisco' => "varchar(255) NULL",

        // ==============================
        // CONTROLE
        // ==============================

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        // ==============================
        // ÍNDICES (EXATOS DO BANCO)
        // ==============================

        'INDEX:idx_nf_serie' => "(nNF, serie_nf)",

        'UNIQUE:uk_identificador' => "(identificador)",

    ]);

};