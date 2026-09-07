<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$dados = [];

$sql = $cnx->prepare("
    SELECT id, codigo_barras, descricao, status
    FROM vest_etiquetas_gerador
    ORDER BY id DESC
");
$sql->execute();
$result = $sql->get_result();

while ($row = $result->fetch_assoc()) {
    $dados[] = [
        "id" => (int) $row['id'],
        "codigo" => $row['codigo_barras'],
        "descricao" => $row['descricao'],
        "status" => $row['status'] ?: 'pendente'
    ];
}

$sql->close();
$cnx->close();

echo json_encode($dados);
