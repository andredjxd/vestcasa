<?php
header('Content-Type: application/json');

include '../assets/_db/db.php';

$dados = [];

$sql = $cnx->query("
    SELECT id, tipo
    FROM vest_avaria_tipo_avaria
    ORDER BY tipo   
");

while ($dad = $sql->fetch_assoc()) {
    $dados[] = [
        "id"        => $dad['id'],
        "tipoava"   => $dad['tipo']
    ];
}

echo json_encode($dados, JSON_UNESCAPED_UNICODE);
