<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__refeitorio', [
        'id' => "int(11) NOT NULL",
        'data' => "date NOT NULL",
        'cod_tipo' => "int(11) NOT NULL",
        'nom_tipo' => "varchar(255) NOT NULL",
        'nr_nota' => "int(11) NOT NULL",
        'codigo' => "int(11) NOT NULL",
        'descricao' => "text NOT NULL",
        'sigla' => "varchar(10) NOT NULL",
        'category' => "varchar(100) NOT NULL",
        'quanti' => "int(11) NOT NULL",
        'valor' => "decimal(15,2) NOT NULL",
    ]);
};
?>