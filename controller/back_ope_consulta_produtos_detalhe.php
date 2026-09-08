<?php
include '../assets/_db/db.php';


$sku = trim($_POST['sku'] ?? '');
// $sku = '154961320999999';
$dados = [];

if (!$sku) {
    echo json_encode([
        "error" => true,
        "message" => "SKU não informado"
    ]);
    exit;
}

$query = "
    SELECT 
        vpc.id,
        vpc.sku, 
        vpc.produto, 
        vpc.marca, 
        vpc.cod_fornecedor, 
        vpc.created_at, 
        IFNULL(vpe.quantidade, 0) as quantidade
    FROM vest_produto_cadastro AS vpc
    LEFT JOIN vest_produto_estoque AS vpe 
        ON vpe.sku = vpc.sku
    WHERE vpc.sku = ?
";

$stmt = $cnx->prepare($query);
$stmt->bind_param("s", $sku);
$stmt->execute();

$res1 = $stmt->get_result();

$estoqueTotal = 0;

if ($res1 && $res1->num_rows > 0) {

    // 👉 como é SKU único, pega direto
    $row = $res1->fetch_assoc();

    $dados = [
        "id"            => $row['id'],
        "sku"           => $row['sku'],
        "produto"       => $row['produto'],
        "marca"         => $row['marca'],
        "codfornecedor" => $row['cod_fornecedor'],
        "quantidade"    => (int)$row['quantidade']
    ];
    $estoqueTotal = (int)$row['quantidade'];

} else {

    $dados = [
        "error" => true,
        "message" => "Produto não encontrado"
    ];
}

$query2 = "
    SELECT qtde, data_recebimento
    FROM vest_produto_extrato 
    WHERE sku = ?
    AND tipo_movimento = 'ENTRADA'
    AND data_recebimento IS NOT NULL  
    ORDER BY data_recebimento DESC 
    LIMIT 1
";

$stmt2 = $cnx->prepare($query2);
$stmt2->bind_param("s", $sku);
$stmt2->execute();

$res2 = $stmt2->get_result();

$dados['dataultimaentrada'] = null;
$dados['ultimaentrada'] = 0;

if ($res2 && $res2->num_rows > 0) {
    $row2 = $res2->fetch_assoc();

    $dados['ultimaentrada'] = (int)$row2['qtde'];
    $dados['dataultimaentrada'] = $row2['data_recebimento'];
}

$query3 = "
    SELECT SUM(qtde) as venda30
    FROM vest_produto_extrato
    WHERE sku = ? AND tipo_movimento = 'SAIDA'
    AND data_movimento >= CURDATE() - INTERVAL 30 DAY
    AND data_movimento < CURDATE() + INTERVAL 1 DAY
    ORDER BY id DESC;
";

$stmt3 = $cnx->prepare($query3);
$stmt3->bind_param("s", $sku);
$stmt3->execute();

$res3 = $stmt3->get_result();

$venda30dias = 0;

if ($res3 && $res3->num_rows > 0) {
    $row2 = $res3->fetch_assoc();
    $venda30dias = (int)$row2['venda30'];

    $dados['venda30'] = $venda30dias;
}

// Calculo Autonomia
if ($venda30dias > 0) {
    $autonomia = ($estoqueTotal * 30) / $venda30dias;
    $dados['autonomia'] = (int)$autonomia;
} else {
    $autonomia = 0; // ou null / 9999
    $dados['autonomia'] = $autonomia;
}

$query4 = "
    SELECT 
        UPPER(DATE_FORMAT(m.mes, '%b')) AS mes,
        IFNULL(SUM(v.qtde), 0) AS total
    FROM (
        -- gera os últimos 6 meses (incluindo atual)
        SELECT DATE_FORMAT(CURDATE(), '%Y-%m-01') AS mes
        UNION ALL
        SELECT DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')
        UNION ALL
        SELECT DATE_FORMAT(CURDATE() - INTERVAL 2 MONTH, '%Y-%m-01')
        UNION ALL
        SELECT DATE_FORMAT(CURDATE() - INTERVAL 3 MONTH, '%Y-%m-01')
        UNION ALL
        SELECT DATE_FORMAT(CURDATE() - INTERVAL 4 MONTH, '%Y-%m-01')
        UNION ALL
        SELECT DATE_FORMAT(CURDATE() - INTERVAL 5 MONTH, '%Y-%m-01')
    ) AS m

    LEFT JOIN vest_produto_extrato v
        ON DATE_FORMAT(v.data_movimento, '%Y-%m-01') = m.mes
        AND v.sku = ?
        AND v.tipo_movimento = 'SAIDA'

    GROUP BY m.mes
    ORDER BY m.mes DESC;
";

$stmt4 = $cnx->prepare($query4);
$stmt4->bind_param("s", $sku);
$stmt4->execute();

$res4 = $stmt4->get_result();

$meses = [];

if ($res4 && $res4->num_rows > 0) {

    while ($row4 = $res4->fetch_assoc()) {

        $meses[] = [
            "mes"   => $row4['mes'],
            "total" => (int)$row4['total']
        ];
    }
}

// adiciona no retorno
$dados['grafico_saida'] = $meses;

$query5 = "
    SELECT vpcb.codigo_barras, vpcb.status, vpc.produto
    FROM vest_produto_codigo_barras as vpcb
    LEFT JOIN vest_produto_cadastro AS vpc ON vpc.sku = vpcb.sku
    WHERE vpc.sku = ?;
";

$stmt5 = $cnx->prepare($query5);
$stmt5->bind_param("s", $sku);
$stmt5->execute();

$res5 = $stmt5->get_result();

$listacodigo = [];

while ($row5 = $res5->fetch_assoc()) {
    $listacodigo[] = [
        "codigo"    => $row5['codigo_barras'],
        "descricao" => $row5['produto'],
        "status"    => $row5['status']
    ];
}

$dados['listacodigobarra'] = $listacodigo;

$query6 = "
        SELECT data_movimento FROM vest_produto_extrato 
        WHERE sku = ? AND tipo_movimento = 'SAIDA'
        ORDER BY data_movimento DESC LIMIT 1;
";

$stmt6 = $cnx->prepare($query6);
$stmt6->bind_param("s", $sku);
$stmt6->execute();

$res6 = $stmt6->get_result();

$ultimavenda = '';

if ($res6 && $res6->num_rows > 0) {
    $row6 = $res6->fetch_assoc();
    $ultimavenda = $row6['data_movimento'];

    $dados['lastsales'] = $ultimavenda;
}

$query7 = "
    SELECT vrpp.sku, vrpp.preco_clube, vrpp.limitacao_clube, vrpp.preco_max, vrpp.limitacao_max, vrpp.preco_varejo, vrpp.limitacao_varejo, vrpp.updated
    FROM vest_relatorio_produto_precos vrpp
    INNER JOIN vest_produto_codigo_barras vpcb
        ON vpcb.codigo_barras = vrpp.codigobarra
        AND vpcb.status = 1
    WHERE vrpp.sku = ?;
";

$stmt7 = $cnx->prepare($query7);
$stmt7->bind_param("s", $sku);
$stmt7->execute();

$res7 = $stmt7->get_result();

$listaprecos = [];

while ($row7 = $res7->fetch_assoc()) {
    $listaprecos[] = [
        "sku"               => $row7['sku'],
        "precoclube"        => $row7['preco_clube'],
        "limitacaoclube"    => $row7['limitacao_clube'],
        "precomax"          => $row7['preco_max'],
        "limitacaomax"      => $row7['limitacao_max'],
        "precovarejo"       => $row7['preco_varejo'],
        "limitacaovarejo"   => $row7['limitacao_varejo'],
        "consulta"          => $row7['updated']
    ];
}

$dados['listaprecos'] = $listaprecos;

header('Content-Type: application/json');
echo json_encode($dados, JSON_UNESCAPED_UNICODE);