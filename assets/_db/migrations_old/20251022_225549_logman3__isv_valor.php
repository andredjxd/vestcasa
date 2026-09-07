<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__isv_valor', [
        'id' => "int(11) NOT NULL",
        'data' => "date NOT NULL",
        'valor_isv' => "double(10,2) NOT NULL",
        'valor_isv_venda' => "double(10,2) NOT NULL",
        'valor_isv_seis' => "double(10,2) NOT NULL",
        'valor_isv_venda_seis' => "double(10,2) NOT NULL",
    ]);
};
?>