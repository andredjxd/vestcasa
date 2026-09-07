<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__pontuacao', [
        'id' => "int(10) NOT NULL",
        'Corredor' => "varchar(100) NOT NULL",
        'isv' => "int(10) NOT NULL",
        'ro' => "int(10) NOT NULL",
        'idade' => "int(10) NOT NULL",
        'total' => "int(10) NOT NULL",
    ]);
};
?>