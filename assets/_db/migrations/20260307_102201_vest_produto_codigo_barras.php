    <?php
    require_once __DIR__ . '/../functions/syncTable.php';

    return function($db) {

       syncTable($db, 'vest_produto_codigo_barras', [

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        'sku' => "varchar(50) NOT NULL",
        'codigo_barras' => "varchar(50) NOT NULL",

        'status' => "int(3) NULL",

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        'INDEX:idx_sku' => "(sku)",
        'INDEX:idx_codigo_sku' => "(codigo_barras, sku)",
        'UNIQUE:codigo' => "(codigo_barras)"

        ]);
    };