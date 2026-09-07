<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__venda_rub', [
        'codigo' => "int(10) NOT NULL",
        'sub_codigo' => "int(3) NOT NULL",
        'descricao' => "varchar(50) NOT NULL",
        'operacao' => "varchar(20) NOT NULL",
        'data' => "date NOT NULL",
    ]);
};
?>