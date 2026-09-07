<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'logman3__boletos', [
        'id' => "int(11) NOT NULL",
        'regional' => "varchar(50) NOT NULL",
        'filial' => "int(11) NOT NULL",
        'razao' => "varchar(255) NOT NULL",
        'cnpj_cpf' => "varchar(20) NOT NULL",
        'titulo' => "int(11) NOT NULL",
        'banco' => "int(11) NOT NULL",
        'datebase' => "date NOT NULL",
        'dataemissao' => "date NOT NULL",
        'vencimento' => "date NOT NULL",
        'pagamento' => "date DEFAULT NULL",
        'valor' => "decimal(10,2) NOT NULL",
        'date_created' => "timestamp NOT NULL",
    ]);
};
?>