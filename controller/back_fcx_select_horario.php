<?php
header('Content-Type: application/json');

include '../assets/_db/db.php';

$dados = [];

$sql = $cnx->query("
    SELECT id, hini, hfim, inter 
    FROM vest_relatorio_colab_horario
    ORDER BY hini
");

while ($dad = $sql->fetch_assoc()) {
    $dados[] = [
        "id"       => $dad['id'],
        "horario"  => $dad['hini'] . ' - ' . $dad['hfim'] . ' - ' . $dad['inter']
    ];
}

echo json_encode($dados, JSON_UNESCAPED_UNICODE);
