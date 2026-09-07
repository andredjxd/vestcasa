<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

$codigoBarras = preg_replace('/\D/', '', $_POST['codigo_barras'] ?? '');

if (!$codigoBarras) {
    echo json_encode([
        "error" => true,
        "message" => "Codigo de barras nao informado"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$query = "
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

$stmt = $cnx->prepare($query);

if (!$stmt) {
    echo json_encode([
        "error" => true,
        "message" => "Erro ao preparar consulta"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param("s", $codigoBarras);
$stmt->execute();

$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    echo json_encode([
        "error" => true,
        "message" => "Mercadoria nao encontrada"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$row = $res->fetch_assoc();

echo json_encode([
    "error" => false,
    "codigo_barras" => $row['codigo_barras'],
    "sku" => $row['sku'],
    "produto" => $row['produto'],
    "marca" => $row['marca'],
    "estoque" => (int)$row['estoque']
], JSON_UNESCAPED_UNICODE);
