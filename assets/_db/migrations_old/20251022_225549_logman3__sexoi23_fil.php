<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__sexoi23_fil', [
        'id' => "int(11) NOT NULL",
        'filial' => "int(11) NOT NULL",
        'Nome' => "varchar(50) NOT NULL",
        'cnpj' => "varchar(14) NOT NULL",
    ]);
};
?>