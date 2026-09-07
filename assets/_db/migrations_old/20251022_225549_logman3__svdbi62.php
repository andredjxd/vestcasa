<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__svdbi62', [
        'data' => "date NOT NULL",
        'grupoNume' => "int(3) NOT NULL",
        'grupoNome' => "varchar(100) NOT NULL",
        'vendaDia' => "double(10,2) NOT NULL",
        'rentDia' => "float NOT NULL",
        'vendaMes' => "double(10,2) NOT NULL",
        'rentMes' => "float NOT NULL",
        'vendaProj' => "double(10,2) NOT NULL",
        'rentProj' => "float NOT NULL",
        'filial' => "int(5) NOT NULL",
    ]);
};
?>