<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__produtos', [
        'codigo' => "int(11) NOT NULL",
        'sub_codigo' => "int(3) NOT NULL",
        'descricao' => "varchar(100) NOT NULL",
        'embDes' => "varchar(5) NOT NULL",
        'emb' => "varchar(15) NOT NULL",
        'codigo_barra' => "bigint(22) NOT NULL",
    ]);
};
?>