<?php
include '../assets/_db/db.php';

$query = "
    SELECT COUNT(DISTINCT vpvb.sku) AS total
    FROM vest_produto_codigo_barras vpvb
    WHERE NOT EXISTS (
        SELECT 1
        FROM vest_produto_codigo_barras x
        WHERE x.sku = vpvb.sku
          AND x.status NOT IN (0)
          AND x.status IS NOT NULL
    )
";

$res = $cnx->query($query);

if ($res) {
    $row = $res->fetch_assoc();

    $retorno = [
        "total" => (int)$row['total']
    ];
} else {
    $retorno = [
        "error"   => "103",
        "message" => "Erro ao executar consulta: " . $cnx->error
    ];
}

header('Content-Type: application/json');
echo json_encode($retorno);