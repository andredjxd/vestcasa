<?php
// Arquivo gerado automaticamente
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'vest_relatorio_produto_precos_historico', [

        'codigobarra'      => "varchar(20) NOT NULL",
        'sku'              => "varchar(50) NOT NULL",
        'descricao'        => "varchar(200) NULL",

        'preco_clube'      => "decimal(10,2) NULL",
        'limitacao_clube'  => "int(11) NULL",

        'preco_max'        => "decimal(10,2) NULL",
        'limitacao_max'    => "int(11) NULL",

        'preco_varejo'     => "decimal(10,2) NULL",
        'limitacao_varejo' => "int(11) NULL",

        'data_registro'    => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",

        'INDEX:idx_codigo' => "(codigobarra)"

    ]);
};


?>
