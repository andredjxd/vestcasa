<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'vest_relatorio_deposito', [
        'id'      => "int(11) unsigned NOT NULL AUTO_INCREMENT",
        'loja'    => "varchar(100) NOT NULL",
        'dt_ini'  => "date NOT NULL",
        'dt_fim'  => "date NOT NULL",
        'status'  => "int(11) NOT NULL",
        'empresa' => "varchar(100) NULL",
        'gvt'     => "int(11) NULL",
        'dt_rec'  => "date NULL",
        'observ'  => "varchar(2000) NULL",
        'created' => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
        'finished' => "datetime NULL"
    ]);
};

?>