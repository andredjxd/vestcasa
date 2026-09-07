<?php
header('Content-Type: application/json');

include '../assets/_db/db.php';

$dados = [];

$sql = $cnx->query("
    SELECT id, nome
    FROM vest_relatorio_colaborador
    WHERE status = 0
    ORDER BY nome
");

while ($dad = $sql->fetch_assoc()) {
    $dados[] = [
        "id"       => $dad['id'],
        "nome"  => $dad['nome']
    ];
}

echo json_encode($dados, JSON_UNESCAPED_UNICODE);
