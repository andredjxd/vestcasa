<?php
include '../assets/_db/db.php';

$dados = [];

$codbarras = $_POST['codbarras'] ?? '';

$where = "";

if (!empty($codbarras)) {
    $codbarras = $cnx->real_escape_string($codbarras);
    $where .= " AND cb.codigo_barras LIKE '%{$codbarras}%'";
}

$query = "
SELECT
    p.id,
    p.sku,
    p.produto,

    CASE
        WHEN IFNULL(vpe.quantidade, 0) = 0 THEN NULL
        ELSE DATEDIFF(CURDATE(), DATE(uv.ultima_venda))
    END AS isv,

    vpp.preco_clube,
    vpp.limitacao_clube,
    vpp.preco_max,
    vpp.limitacao_max,
    vpp.preco_varejo

FROM vest_produto_cadastro p

INNER JOIN vest_produto_codigo_barras cb
    ON cb.sku = p.sku

LEFT JOIN (
    SELECT
        vrvi.codigo_produto,
        MAX(vrv.data_hora) AS ultima_venda
    FROM vest_relatorio_vendas_itens vrvi
    INNER JOIN vest_relatorio_vendas vrv
        ON vrv.identificador = vrvi.identificadornf
    GROUP BY vrvi.codigo_produto
) uv
    ON uv.codigo_produto = p.sku

LEFT JOIN vest_produto_estoque vpe
    ON vpe.sku = p.sku

LEFT JOIN vest_relatorio_produto_precos vpp
    ON vpp.sku = p.sku

WHERE 1=1
{$where}

GROUP BY p.sku
ORDER BY p.produto ASC;
";


$res = $cnx->query($query);

if ($res) {
    while ($rowProd = $res->fetch_assoc()) {

        $dados[] = [
            "id"          => $rowProd['id'],
            "sku"         => $rowProd['sku'],
            "produto"     => $rowProd['produto'],
            "isv"         => $rowProd['isv'],
            "precoclube"  => $rowProd['preco_clube'],
            "limclube"    => $rowProd['limitacao_clube'],
            "precomax"    => $rowProd['preco_max'],
            "limmax"      => $rowProd['limitacao_max'],
            "precovarejo" => $rowProd['preco_varejo']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($dados, JSON_UNESCAPED_UNICODE);