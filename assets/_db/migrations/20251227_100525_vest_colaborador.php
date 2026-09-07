<?php

require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'vest_relatorio_colaborador', [
        'id'        => "int(11) unsigned NOT NULL AUTO_INCREMENT",
        'codigo'    => "int(11) NULL",
        'nome'      => "varchar(200) NOT NULL",
        'admissao'  => "date NULL",
        'cpf'       => "varchar(20) NULL",
        'idfuncao'    => "int(3) NULL",
        'idhorario'    => "int(3) NULL",
        'status' => "int(11) DEFAULT 0"
    ]);
};

?>