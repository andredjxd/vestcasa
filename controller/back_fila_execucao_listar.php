<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$sql = $cnx->query("
    SELECT 
        id,
        nome_processo,
        total,
        processados,
        progresso,
        status,
        log,
        data_inicio,
        data_fim,
        created
    FROM vest_relatorio_fila_execucao
    ORDER BY id DESC
    LIMIT 10
");

$dados = [];

while ($row = $sql->fetch_assoc()) {

    $status_texto = 'Fila';

    if ($row['status'] == 1) $status_texto = 'Executando';
    if ($row['status'] == 2) $status_texto = 'Concluído';
    if ($row['status'] == 3) $status_texto = 'Erro';

    $dados[] = [
        "id" => (int)$row['id'],
        "nome_processo" => $row['nome_processo'],
        "total" => (int)$row['total'],
        "processados" => (int)$row['processados'],
        "progresso" => (float)$row['progresso'],
        "status" => $row['status'],
        "status_texto" => $status_texto,
        "log" => $row['log'],
        "data_inicio" => $row['data_inicio'],
        "data_fim" => $row['data_fim'],
        "created" => $row['created']
    ];
}

echo json_encode($dados);
