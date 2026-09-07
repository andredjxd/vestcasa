<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman__funcoes', [
        'id' => "int(10) unsigned NOT NULL",
        'relatorio_nome' => "varchar(10) NOT NULL",
        'nome_tela' => "varchar(65) NOT NULL",
        'filial1' => "int(3) NOT NULL",
        'filial2' => "int(3) NOT NULL",
        'data1' => "varchar(12) NOT NULL",
        'data2' => "varchar(12) NOT NULL",
        'flutuante1' => "varchar(15) NOT NULL",
        'flutuante2' => "varchar(15) NOT NULL",
        'inteiro1' => "int(12) NOT NULL",
        'inteiro2' => "int(12) NOT NULL",
        'string' => "varchar(32) NOT NULL",
        'tela' => "int(1) NOT NULL",
        'abreviatura' => "varchar(8) DEFAULT NULL",
        'orientacao' => "char(1) DEFAULT NULL",
        'TamanhoFonte' => "double DEFAULT NULL",
        'Margem' => "double DEFAULT NULL",
        'executar' => "int(11) DEFAULT NULL",
    ]);
};
?>