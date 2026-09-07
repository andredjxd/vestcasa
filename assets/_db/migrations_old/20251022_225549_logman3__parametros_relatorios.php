<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__parametros_relatorios', [
        'id' => "int(11) NOT NULL",
        'pos' => "int(11) NOT NULL",
        'nome' => "varchar(100) DEFAULT NULL",
        'operador01' => "varchar(100) NOT NULL",
        'dias01' => "int(11) NOT NULL",
        'operador02' => "varchar(100) NOT NULL",
        'dias02' => "int(11) NOT NULL",
        'opelogico' => "varchar(50) NOT NULL",
        'setores' => "varchar(200) NOT NULL",
        'setor_nome' => "varchar(50) NOT NULL",
        'tipo' => "varchar(50) DEFAULT NULL",
    ]);
};
?>