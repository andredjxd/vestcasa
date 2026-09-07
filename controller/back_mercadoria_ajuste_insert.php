<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

$codigoBarras = preg_replace('/\D/', '', $_POST['codigo_barras'] ?? '');
$quantidade = filter_var($_POST['quantidade_ajuste'] ?? null, FILTER_VALIDATE_INT);
$observacao = trim($_POST['observacao'] ?? '');
$usuario = gethostname();

if (!$codigoBarras) {
    echo json_encode([
        "status" => "error",
        "message" => "Codigo de barras nao informado"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($quantidade === false || $quantidade === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Informe uma quantidade de ajuste diferente de zero"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$queryProduto = "
    SELECT
        cb.codigo_barras,
        p.sku,
        p.produto,
        p.marca,
        IFNULL(e.quantidade, 0) AS estoque
    FROM vest_produto_codigo_barras cb
    INNER JOIN vest_produto_cadastro p
        ON p.sku = cb.sku
    LEFT JOIN vest_produto_estoque e
        ON e.sku = p.sku
    WHERE cb.codigo_barras = ?
    LIMIT 1
";

$stmtProduto = $cnx->prepare($queryProduto);
$stmtProduto->bind_param("s", $codigoBarras);
$stmtProduto->execute();
$resProduto = $stmtProduto->get_result();

if (!$resProduto || $resProduto->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Mercadoria nao encontrada"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$produto = $resProduto->fetch_assoc();
$estoqueAnterior = (int)$produto['estoque'];

$queryInsert = "
    INSERT INTO vest_mercadoria_ajuste
        (
            codigo_barras,
            sku,
            produto,
            marca,
            quantidade_ajuste,
            estoque_anterior,
            usuario_criacao,
            observacao
        )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $cnx->prepare($queryInsert);
$stmt->bind_param(
    "ssssiiss",
    $produto['codigo_barras'],
    $produto['sku'],
    $produto['produto'],
    $produto['marca'],
    $quantidade,
    $estoqueAnterior,
    $usuario,
    $observacao
);

if (!$stmt->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "Erro ao adicionar item: " . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Item adicionado para ajuste"
], JSON_UNESCAPED_UNICODE);
