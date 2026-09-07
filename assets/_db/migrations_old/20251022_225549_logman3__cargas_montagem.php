<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__cargas_montagem', [
        'id' => "int(11) NOT NULL",
        'id_carga' => "int(11) NOT NULL",
        'filial' => "int(7) NOT NULL",
        'codigo' => "int(11) NOT NULL",
        'setorNum' => "int(11) NOT NULL",
        'descricao' => "varchar(50) NOT NULL",
        'emb' => "varchar(50) NOT NULL",
        'peso' => "float NOT NULL",
        'aut' => "int(11) NOT NULL",
        'qtdpal' => "float NOT NULL",
        'qtd' => "int(11) NOT NULL",
        'status' => "int(11) DEFAULT NULL",
    ]);
};
?>