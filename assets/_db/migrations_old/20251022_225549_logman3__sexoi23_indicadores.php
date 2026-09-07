<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__sexoi23_indicadores', [
        'id' => "int(11) NOT NULL",
        'data' => "date NOT NULL",
        'total_skus' => "int(11) NOT NULL",
        'total_skus_fil001' => "int(11) NOT NULL",
        'total_skus_fil002' => "int(11) NOT NULL",
        'total_skus_fil003' => "int(11) NOT NULL",
        'total_skus_aut' => "int(11) NOT NULL",
        'total_skus_aut_fil001' => "int(11) NOT NULL",
        'total_skus_aut_fil002' => "int(11) NOT NULL",
        'total_skus_aut_fil003' => "int(11) NOT NULL",
        'valor_total' => "double(10,2) NOT NULL",
        'valor_total_fil001' => "double(10,2) NOT NULL",
        'valor_total_fil002' => "double(10,2) NOT NULL",
        'valor_total_fil003' => "double(10,2) NOT NULL",
        'valor_total_aut' => "double(10,2) NOT NULL",
        'valor_total_aut_fil001' => "double(10,2) NOT NULL",
        'valor_total_aut_fil002' => "double(10,2) NOT NULL",
        'valor_total_aut_fil003' => "double(10,2) NOT NULL",
    ]);
};
?>