    <?php
    require_once __DIR__ . '/../functions/syncTable.php';

    return function($db) {

       syncTable($db, 'vest_produto', [

        'id' => "int(11) NOT NULL AUTO_INCREMENT",

        'sku' => "varchar(50) NOT NULL",
        'nome' => "varchar(255) NULL",
        'marca' => "varchar(150) NULL",
        'fornecedor_id' => "int(11) NULL",

        // 0 venda
        // 1 interno
        // 2 fl
        'status' => "int(1) DEFAULT 0",

        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",

        'UNIQUE:sku' => "(sku)"

        ]);
    };