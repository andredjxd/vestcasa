<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

// ================================
// 📥 Receber e validar data
// ================================
$data = $_POST['data'] ?? null;

if (!$data) {
    echo json_encode(["erro" => "Data não enviada"]);
    exit;
}

// Remove hífen caso venha no formato Y-m-d
$data = str_replace('-', '', $data);

// Valida formato Ymd (8 números)
if (!preg_match('/^\d{8}$/', $data)) {
    echo json_encode(["erro" => "Formato de data inválido"]);
    exit;
}

// ================================
// 📅 Criar intervalo da data
// ================================
$data_base = DateTime::createFromFormat('Ymd', $data);

if (!$data_base) {
    echo json_encode(["erro" => "Data inválida"]);
    exit;
}

// Início do dia
$data_base->setTime(0, 0, 0);
$dtIni = $data_base->format('Y-m-d H:i:s');

// Fim (dia seguinte)
$data_base->modify('+1 day');
$dtFim = $data_base->format('Y-m-d H:i:s');

// ================================
// 📦 Array retorno
// ================================
$dados = [];
$pos = 1;

// ================================
// 📊 Consulta
// ================================
$sql = $cnx->prepare("
SELECT
    sku,
    codigobarra,
    descricao,
    data_registro,

    preco_clube,
    preco_clube_anterior,
    limitacao_clube,
    limitacao_clube_anterior,

    preco_max,
    preco_max_anterior,
    limitacao_max,
    limitacao_max_anterior,

    preco_varejo,
    preco_varejo_anterior,
    limitacao_varejo,
    limitacao_varejo_anterior,

    CASE
        WHEN preco_clube > preco_clube_anterior THEN 'AUMENTOU'
        WHEN preco_clube < preco_clube_anterior THEN 'ABAIXOU'
        ELSE 'IGUAL'
    END AS status_clube,

    CASE
        WHEN limitacao_clube > limitacao_clube_anterior THEN 'AUMENTOU'
        WHEN limitacao_clube < limitacao_clube_anterior THEN 'ABAIXOU'
        ELSE 'IGUAL'
    END AS status_clube_limite,

    CASE
        WHEN preco_max > preco_max_anterior THEN 'AUMENTOU'
        WHEN preco_max < preco_max_anterior THEN 'ABAIXOU'
        ELSE 'IGUAL'
    END AS status_max,

    CASE
        WHEN limitacao_max > limitacao_max_anterior THEN 'AUMENTOU'
        WHEN limitacao_max < limitacao_max_anterior THEN 'ABAIXOU'
        ELSE 'IGUAL'
    END AS status_max_limite,

    CASE
        WHEN preco_varejo > preco_varejo_anterior THEN 'AUMENTOU'
        WHEN preco_varejo < preco_varejo_anterior THEN 'ABAIXOU'
        ELSE 'IGUAL'
    END AS status_varejo,

    CASE
        WHEN limitacao_varejo > limitacao_varejo_anterior THEN 'AUMENTOU'
        WHEN limitacao_varejo < limitacao_varejo_anterior THEN 'ABAIXOU'
        ELSE 'IGUAL'
    END AS status_varejo_limite

FROM (
    SELECT
        sku,
        codigobarra,
        descricao,
        data_registro,

        preco_clube,
        LAG(preco_clube) OVER (
            PARTITION BY sku
            ORDER BY data_registro
        ) AS preco_clube_anterior,

        limitacao_clube,
        LAG(limitacao_clube) OVER (
            PARTITION BY sku
            ORDER BY data_registro
        ) AS limitacao_clube_anterior,

        preco_max,
        LAG(preco_max) OVER (
            PARTITION BY sku
            ORDER BY data_registro
        ) AS preco_max_anterior,

        limitacao_max,
        LAG(limitacao_max) OVER (
            PARTITION BY sku
            ORDER BY data_registro
        ) AS limitacao_max_anterior,

        preco_varejo,
        LAG(preco_varejo) OVER (
            PARTITION BY sku
            ORDER BY data_registro
        ) AS preco_varejo_anterior,

        limitacao_varejo,
        LAG(limitacao_varejo) OVER (
            PARTITION BY sku
            ORDER BY data_registro
        ) AS limitacao_varejo_anterior

    FROM (
        -- mantem apenas o ultimo snapshot de cada sku por dia,
        -- evitando que 2+ consultas no mesmo dia gerem 2 alteracoes no mesmo dia
        SELECT
            sku, codigobarra, descricao, data_registro,
            preco_clube, limitacao_clube,
            preco_max, limitacao_max,
            preco_varejo, limitacao_varejo
        FROM (
            SELECT
                sku, codigobarra, descricao, data_registro,
                preco_clube, limitacao_clube,
                preco_max, limitacao_max,
                preco_varejo, limitacao_varejo,
                ROW_NUMBER() OVER (
                    PARTITION BY sku, DATE(data_registro)
                    ORDER BY data_registro DESC
                ) AS rn
            FROM vest_relatorio_produto_precos_historico
        ) ultimo_do_dia
        WHERE rn = 1
    ) AS dedup_por_dia
) AS historico

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

ORDER BY descricao ASC
");

if (!$sql) {
    echo json_encode(["erro" => $cnx->error]);
    exit;
}

$sql->bind_param("ss", $dtIni, $dtFim);
$sql->execute();
$result = $sql->get_result();

// ================================
// 🔄 Processamento
// ================================
while ($row = $result->fetch_assoc()) {

    $listacodigosbarra = [];

    $query2 = "
        SELECT codigo_barras
        FROM vest_produto_codigo_barras
        WHERE sku = ?
        ORDER BY (status = 1) DESC
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

    // $listacodigosbarra = implode('<br>', $listacodigosbarra); // Formatar para ficar um de baixo do outro

    $estoque = 0;

    $query3 = "
        SELECT quantidade
        FROM vest_produto_estoque
        WHERE sku = ?
        LIMIT 1
    ";

    $stmt3 = $cnx->prepare($query3);
    $stmt3->bind_param("s", $row['sku']);
    $stmt3->execute();

    $res3 = $stmt3->get_result();

    if ($res3 && $row3 = $res3->fetch_assoc()) {
        $estoque = (int) $row3['quantidade'];
    }

    $dados[] = [
        "pos" => $pos,
        "sku" => $row['sku'],
        "codigobarra" =>  $listacodigosbarra,
        "descricao" => $row['descricao'],
        "estoque" => $estoque,
        "data_registro" => $row['data_registro'],

        "preco_clube_anterior" => (float)$row['preco_clube_anterior'],
        "preco_clube" => (float)$row['preco_clube'],
        "status_clube" => $row['status_clube'],

        "limitacao_clube_anterior" => (int)$row['limitacao_clube_anterior'],
        "limitacao_clube" => (int)$row['limitacao_clube'],
        "status_clube_limite" => $row['status_clube_limite'],

        "preco_max_anterior" => (float)$row['preco_max_anterior'],
        "preco_max" => (float)$row['preco_max'],
        "status_max" => $row['status_max'],

        "limitacao_max_anterior" => (int)$row['limitacao_max_anterior'],
        "limitacao_max" => (int)$row['limitacao_max'],
        "status_max_limite" => $row['status_max_limite'],

        "preco_varejo_anterior" => (float)$row['preco_varejo_anterior'],
        "preco_varejo" => (float)$row['preco_varejo'],
        "status_varejo" => $row['status_varejo']
    ];

    $pos++;
}

// ================================
// 🔒 Fechamento
// ================================
$sql->close();
$cnx->close();

// ================================
// 📤 Retorno JSON
// ================================
echo json_encode($dados);
