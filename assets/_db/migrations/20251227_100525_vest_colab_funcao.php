<?php

require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'vest_relatorio_colab_funcao', [
        'id'      => "int(11) unsigned NOT NULL AUTO_INCREMENT",
        'funcao'    => "varchar(100) NOT NULL",
        'created'   => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP"
    ]);
};

?>