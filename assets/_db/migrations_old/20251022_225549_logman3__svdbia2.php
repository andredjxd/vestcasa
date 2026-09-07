<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__svdbia2', [
        'data' => "int(11) NOT NULL",
        'cod_comprador' => "int(11) NOT NULL",
        'nom_comprador' => "varchar(100) NOT NULL",
        'cpfcnpj' => "varchar(20) NOT NULL",
        'razaosocial' => "varchar(200) NOT NULL",
        'codigo' => "int(11) NOT NULL",
        'sub' => "int(11) NOT NULL",
        'descricao' => "varchar(200) NOT NULL",
        'mult1' => "int(11) NOT NULL",
        'mult2' => "int(11) NOT NULL",
        'emb1' => "int(11) NOT NULL",
        'emb2' => "int(11) NOT NULL",
        'datainicial' => "date NOT NULL",
        'datafinal' => "date NOT NULL",
        'qtdverba' => "int(11) NOT NULL",
        'vlrverba' => "double(10,2) NOT NULL",
        'qtdmovimento' => "int(11) NOT NULL",
        'vlrmovimento' => "double(10,2) NOT NULL",
        'canalvenda' => "varchar(100) NOT NULL",
    ]);
};
?>