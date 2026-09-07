<?php
// Arquivo gerado automaticamente em 2025-10-22 22:55:49
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'vest_relatorio_fechamento', [
        'id'             => "int(11) unsigned NOT NULL AUTO_INCREMENT",
        'dt_fecha'       => "date NOT NULL",
        'operador'       => "varchar(100) NULL",
        'pdv'            => "int(3) NOT NULL",
        'saldoini'       => "decimal(10,2) NOT NULL",
        'saldocartao'    => "decimal(10,2) NULL",
        'pos'            => "int(3) NOT NULL",
        'saldofinal'     => "decimal(10,2) NOT NULL",
        'valor_deposito' => "decimal(10,2) NULL",
        'valor_sangria'  => "decimal(10,2) NULL",
        'quebra'         => "decimal(10,2) NULL",
        'trocafundo'     => "decimal(10,2) NULL",
        'observ'         => "varchar(2000) NULL",
        'created' => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
        
        'INDEX:idx_dt_fecha' => "(dt_fecha)",
        'INDEX:idx_dt_fecha_total' => "(dt_fecha, saldocartao, valor_deposito, valor_sangria)",
    ]);
};

?>