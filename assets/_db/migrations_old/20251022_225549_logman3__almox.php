<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__almox', [
        'id' => "int(11) NOT NULL",
        'codDep' => "int(11) DEFAULT NULL",
        'DesDep' => "varchar(255) DEFAULT NULL",
        'conta' => "int(11) DEFAULT NULL",
        'rol' => "int(11) DEFAULT NULL",
        'descricao' => "varchar(255) DEFAULT NULL",
        'data' => "date DEFAULT NULL",
        'user' => "varchar(100) DEFAULT NULL",
        'req' => "int(11) DEFAULT NULL",
        'codigo' => "int(11) DEFAULT NULL",
        'codDescri' => "varchar(100) DEFAULT NULL",
        'Qtd' => "int(11) DEFAULT NULL",
        'valor' => "decimal(10,2) DEFAULT NULL",
    ]);
};
?>