<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

$status = $_POST['status'] ?? 'pendente';
$dados = [];

$where = "WHERE status = 1";

if ($status === 'relatorio') {
    $where = "WHERE status IN (1, 2)";
} elseif ($status === 'finalizado') {
    $where = "WHERE status = 2";
}

$query = "
    SELECT
        id,
        codigo_barras,
        sku,
        produto,
        marca,
        quantidade_ajuste,
        estoque_anterior,
        estoque_posterior,
        status,
        usuario_criacao,
        usuario_ajuste,
        observacao,
        data_ajuste,
        created_at
    FROM vest_mercadoria_ajuste
    {$where}
    ORDER BY
        CASE WHEN status = 1 THEN 0 ELSE 1 END,
        id DESC
";

$res = $cnx->query($query);

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $dados[] = [
            "id" => (int)$row['id'],
            "codigo_barras" => $row['codigo_barras'],
            "sku" => $row['sku'],
            "produto" => $row['produto'],
            "marca" => $row['marca'],
            "quantidade_ajuste" => (int)$row['quantidade_ajuste'],
            "estoque_anterior" => $row['estoque_anterior'] === null ? null : (int)$row['estoque_anterior'],
            "estoque_posterior" => $row['estoque_posterior'] === null ? null : (int)$row['estoque_posterior'],
            "status" => (int)$row['status'],
            "usuario_criacao" => $row['usuario_criacao'],
            "usuario_ajuste" => $row['usuario_ajuste'],
            "observacao" => $row['observacao'],
            "data_ajuste" => $row['data_ajuste'],
            "created_at" => $row['created_at']
        ];
    }
}

echo json_encode($dados, JSON_UNESCAPED_UNICODE);
