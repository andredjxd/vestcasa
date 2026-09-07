<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__despesa_empresas', [
        'id' => "int(11) NOT NULL",
        'cnpj' => "varchar(20) NOT NULL",
        'razao_social' => "varchar(200) NOT NULL",
    ]);
};
?>