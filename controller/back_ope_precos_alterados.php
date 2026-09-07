<?php
include '../assets/_db/db.php';

$ini = $_POST['ini'];
$fim = $_POST['fim'];

// $ini = '20260501';
// $fim = '20260531';

if (!$ini || !$fim) {
    die(json_encode(["erro" => "Parâmetros obrigatórios não enviados"]));
}

// ================================
// 📅 Conversão datas
// ================================
$data_ini = DateTime::createFromFormat('Ymd', $ini);
$data_fim = DateTime::createFromFormat('Ymd', $fim);

if (!$data_ini || !$data_fim) {
    die(json_encode(["erro" => "Datas inválidas"]));
}

$data_ini->setTime(0, 0, 0);
$data_fim->setTime(23, 59, 59);
$data_fim->modify('-1 day');

$dtIni = $data_ini->format('Y-m-d H:i:s');
$dtFim = $data_fim->format('Y-m-d H:i:s');

$dados = [];
$cadastrosPorDia = [];
$alteracoesPorDia = [];
$horaUltimaAlteracaoPorDia = [];

// ================================
// 🔹 0) Total geral de produtos cadastrados
// ================================
$sqlTotal = $cnx->prepare("
    SELECT COUNT(*) AS totalprodutos
    FROM vest_relatorio_produto_precos
");

$sqlTotal->execute();
$resTotal = $sqlTotal->get_result();
$rowTotal = $resTotal->fetch_assoc();

$totalProdutosGeral = (int) ($rowTotal['totalprodutos'] ?? 0);

$sqlTotal->close();

// ================================
// 🔹 1) Produtos cadastrados por dia
// ================================
$sqlProd = $cnx->prepare("
SELECT 
    DATE(created) AS data,
    COUNT(*) AS qtdprodutos
FROM vest_relatorio_produto_precos
WHERE created >= ?
AND created <= ?
GROUP BY DATE(created)
ORDER BY data
");

$sqlProd->bind_param("ss", $dtIni, $dtFim);
$sqlProd->execute();
$resProd = $sqlProd->get_result();

while ($row = $resProd->fetch_assoc()) {
    $cadastrosPorDia[$row['data']] = (int)$row['qtdprodutos'];
}
$sqlProd->close();

// ================================
// 🔹 2) Alterações por dia
// ================================
$sqlAlt = $cnx->prepare("
SELECT
    DATE(data_registro) AS data,
    COUNT(*) AS quantidade_alterados,
    MAX(data_registro) AS ultima_alteracao
FROM (
    SELECT
        codigobarra,
        data_registro,

        LAG(preco_clube) OVER (PARTITION BY codigobarra ORDER BY data_registro) AS preco_clube_anterior,
        LAG(limitacao_clube) OVER (PARTITION BY codigobarra ORDER BY data_registro) AS limitacao_clube_anterior,
        LAG(preco_max) OVER (PARTITION BY codigobarra ORDER BY data_registro) AS preco_max_anterior,
        LAG(limitacao_max) OVER (PARTITION BY codigobarra ORDER BY data_registro) AS limitacao_max_anterior,
        LAG(preco_varejo) OVER (PARTITION BY codigobarra ORDER BY data_registro) AS preco_varejo_anterior,
        LAG(limitacao_varejo) OVER (PARTITION BY codigobarra ORDER BY data_registro) AS limitacao_varejo_anterior,

        preco_clube,
        preco_max,
        preco_varejo,
        limitacao_clube,
        limitacao_max,
        limitacao_varejo

    FROM (
        -- mantem apenas o ultimo snapshot de cada codigobarra por dia,
        -- evitando que 2+ consultas no mesmo dia gerem 2 alteracoes no mesmo dia
        SELECT
            codigobarra, data_registro,
            preco_clube, limitacao_clube,
            preco_max, limitacao_max,
            preco_varejo, limitacao_varejo
        FROM (
            SELECT
                codigobarra, data_registro,
                preco_clube, limitacao_clube,
                preco_max, limitacao_max,
                preco_varejo, limitacao_varejo,
                ROW_NUMBER() OVER (
                    PARTITION BY codigobarra, DATE(data_registro)
                    ORDER BY data_registro DESC
                ) AS rn
            FROM vest_relatorio_produto_precos_historico
        ) ultimo_do_dia
        WHERE rn = 1
    ) AS dedup_por_dia
) h

WHERE 
    data_registro >= ?
    AND data_registro < ?
    AND preco_varejo_anterior IS NOT NULL
    AND (
        preco_varejo <> preco_varejo_anterior
        OR limitacao_varejo <> limitacao_varejo_anterior
        OR preco_clube <> preco_clube_anterior
        OR limitacao_clube <> limitacao_clube_anterior
        OR preco_max <> preco_max_anterior
        OR limitacao_max <> limitacao_max_anterior
    )

GROUP BY DATE(data_registro)
");

$sqlAlt->bind_param("ss", $dtIni, $dtFim);
$sqlAlt->execute();
$resAlt = $sqlAlt->get_result();

while ($row = $resAlt->fetch_assoc()) {
    $alteracoesPorDia[$row['data']] = (int)$row['quantidade_alterados'];
    $horaUltimaAlteracaoPorDia[$row['data']] = $row['ultima_alteracao'];
}
$sqlAlt->close();

// ================================
// 🔹 3) Montar lista de dias
// ================================
$periodo = new DatePeriod(
    new DateTime($dtIni),
    new DateInterval('P1D'),
    new DateTime($dtFim)
);

$totalAcumulado = 0;

foreach ($periodo as $dataObj) {

    $data = $dataObj->format('Y-m-d');

    $qtdDia = $cadastrosPorDia[$data] ?? 0;
    $totalAcumulado += $qtdDia;

    $alterados = $alteracoesPorDia[$data] ?? 0;

    $percentual = ($totalProdutosGeral > 0)
        ? round(($alterados / $totalProdutosGeral) * 100, 2)
        : 0;

    if ($qtdDia > 0 || $alterados > 0) {

        $dados[] = [
            "data" => $data,
            "qtdprodutos" => $qtdDia,
            "totalprodutos" => $totalProdutosGeral,
            "quantidade_alterados" => $alterados,
            "percentual_alterado" => $percentual,
            "hora_ultima_alteracao" => $horaUltimaAlteracaoPorDia[$data] ?? null
        ];
    }
}

// ================================
// 🔒 Fechamento
// ================================
$cnx->close();

header('Content-Type: application/json');
echo json_encode($dados);
