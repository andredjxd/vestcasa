<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'vest_relatorio_produto_codigobarra', [
        'id'             => "int(11) unsigned NOT NULL AUTO_INCREMENT",
        'codigobarra'    => "varchar(20) NOT NULL",
        'descricao'      => "varchar(200) NULL",
        'status'         => "tinyint(1) NOT NULL DEFAULT 0",
        'created'        => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP"
    ]);
};

?>