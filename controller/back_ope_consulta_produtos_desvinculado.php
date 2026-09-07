<?php
include '../assets/_db/db.php';

$dados = [];

$query = "
    SELECT 
        vpvb.id,
        vpvb.sku,
        vpvb.codigo_barras,
        vpvb.created_at,
        vpvb.status,
        vpc.produto
    FROM vest_produto_codigo_barras vpvb
    LEFT JOIN vest_produto_cadastro vpc 
        ON vpc.sku = vpvb.sku
    WHERE NOT EXISTS (
        SELECT 1
        FROM vest_produto_codigo_barras x
        WHERE x.sku = vpvb.sku
          AND x.status NOT IN (0)
          AND x.status IS NOT NULL
    )
    GROUP BY vpvb.sku
    ORDER BY vpvb.sku ASC
";

$res1 = $cnx->query($query);

if ($res1) {
    $pos = 0;

    while ($row = $res1->fetch_assoc()) {
        $pos++;

        $listacodigosbarra = [];

        $query2 = "
            SELECT codigo_barras
            FROM vest_produto_codigo_barras 
            WHERE sku = ?
        ";

        $stmt2 = $cnx->prepare($query2);
        $stmt2->bind_param("s", $row['sku']);
        $stmt2->execute();

        $res2 = $stmt2->get_result();

        if ($res2 && $res2->num_rows > 0) {

            while ($row2 = $res2->fetch_assoc()) {
                $listacodigosbarra[] = $row2['codigo_barras'];
            }

        }

        $dados[] = [
            "id"            => $row['id'],
            "sku"           => $row['sku'],
            "codigo"        => $listacodigosbarra,
            "produto"       => $row['produto'],
            "status"        => $row['status'],
            "data"          => $row['created_at']
        ];
    }

} else {
    $dados = [
        "error"   => "103",
        "message" => "Erro ao executar consulta: " . $cnx->error
    ];
}

header('Content-Type: application/json');
echo json_encode($dados, JSON_UNESCAPED_UNICODE);