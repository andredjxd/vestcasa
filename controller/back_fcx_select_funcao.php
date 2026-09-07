<?php
header('Content-Type: application/json');

include '../assets/_db/db.php';

$dados = [];

$sql = $cnx->query("
    SELECT id, funcao
    FROM vest_relatorio_colab_funcao
    ORDER BY funcao
");

while ($dad = $sql->fetch_assoc()) {
    $dados[] = [
        "id"     => $dad['id'],
        "funcao" => $dad['funcao']
    ];
}

echo json_encode($dados, JSON_UNESCAPED_UNICODE);
