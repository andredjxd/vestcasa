<?php

require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'vest_relatorio_colab_horario', [
        'id'      => "int(11) unsigned NOT NULL AUTO_INCREMENT",
        'hini'    => "varchar(10) NOT NULL",
        'hfim'    => "varchar(10) NOT NULL",
        'inter'   => "varchar(10) NOT NULL",
        'created'   => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP"
    ]);
};

?>