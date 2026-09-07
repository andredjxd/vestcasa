<?php
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {

    syncTable($db, 'vest_rme_recebimento_divergencias', [

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        'recebimento_id' => "int(11) NOT NULL",

        'nota' => "varchar(50) NOT NULL",

        'divergencia' => "text NULL",

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        'INDEX:idx_recebimento' => "(recebimento_id)",

        'UNIQUE:uniq_nota' => "(nota)"

    ]);

};