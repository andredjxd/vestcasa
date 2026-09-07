<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__cargas', [
        'id_carga' => "int(11) NOT NULL",
        'filial' => "int(11) NOT NULL",
        'nome' => "varchar(50) NOT NULL",
        'status' => "int(2) NOT NULL",
        'peso' => "float NOT NULL",
        'criada_date' => "date NOT NULL",
        'criada_hora' => "time NOT NULL",
        'finalizada_data' => "date NOT NULL",
        'finalizada_hora' => "time NOT NULL",
    ]);
};
?>