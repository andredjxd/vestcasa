<?php
// Arquivo gerado em 2025-12-27 19:59:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'vest_relatorio_deposito_itens', [
        'id'        => "int(11) unsigned NOT NULL AUTO_INCREMENT",
        'idrel'     => "int(11) unsigned NOT NULL",
        'data'      => "date NOT NULL",
        'pdv'       => "int(11) NOT NULL",
        'operador'  => "int(11) NOT NULL",
        'lideranca' => "varchar(100) NOT NULL",
        'valor'     => "decimal(10,2) NOT NULL",
        'numbanana' => "int(11) NOT NULL",
        'created'   => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP"
    ]);
};

?>