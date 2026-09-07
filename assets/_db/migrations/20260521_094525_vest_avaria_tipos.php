<?php

require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'vest_avaria_tipo_avaria', [
        'id'        => "int(11) unsigned NOT NULL AUTO_INCREMENT",
        'tipo'      => "varchar(100) NOT NULL",
        'status'    => "int(1) DEFAULT 0",
        'created'   => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP"
    ]);
};

?>