<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__sexoi23_cargas', [
        'id' => "int(11) NOT NULL",
        'filial' => "int(11) DEFAULT NULL",
        'codigo' => "int(11) DEFAULT NULL",
        'descricao' => "varchar(100) DEFAULT NULL",
        'emb' => "varchar(50) DEFAULT NULL",
        'qtd' => "int(11) DEFAULT NULL",
    ]);
};
?>