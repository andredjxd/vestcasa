<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$sku = trim($_POST['sku'] ?? '');

// 👉 REMOVA isso em produção
// $sku = '132551320999999';

if (!$sku) {
    echo json_encode([
        "error" => true,
        "message" => "SKU não informado"
    ]);
    exit;
}

$query = "
    SELECT 
        data,
        vpml.descricao,
        SUM(vpe.qtde) AS total_qtde
    FROM (
        SELECT 
            vpe.sku,
            vpe.documento,
            vpe.qtde,
            DATE(
                CASE 
                    WHEN vpe.identificador IS NULL THEN vpe.data_recebimento
                    ELSE vpe.data_movimento
                END
            ) AS data
        FROM vest_produto_extrato vpe
        WHERE vpe.sku = ?
    ) vpe
    INNER JOIN vest_produto_movimentacao_log vpml 
        ON vpml.sku = vpe.sku 
        AND vpml.documento = vpe.documento
    GROUP BY data, vpml.descricao
    ORDER BY data DESC;
";

$stmt = $cnx->prepare($query);

if (!$stmt) {
    echo json_encode([
        "error" => true,
        "message" => "Erro ao preparar query"
    ]);
    exit;
}

$stmt->bind_param("s", $sku);

if (!$stmt->execute()) {
    echo json_encode([
        "error" => true,
        "message" => "Erro ao executar query"
    ]);
    exit;
}

$res1 = $stmt->get_result();

$dados = [];

if ($res1 && $res1->num_rows > 0) {

    while ($row = $res1->fetch_assoc()) {

        $dados[] = [
            "data"     => $row['data'],
            "total"    => (float)$row['total_qtde'], // 👈 já retorna número
            "produto"  => $row['descricao']
        ];
    }

} else {

    echo json_encode([
        "error" => true,
        "message" => "Nenhum resultado encontrado"
    ]);
    exit;
}

echo json_encode($dados, JSON_UNESCAPED_UNICODE);