<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode([
        "error" => "100",
        "message" => "ID não informado"
    ]);
    exit;
}

try {

    /* 🔎 Verifica se existe na fila */
    $check = $cnx->prepare("
        SELECT log, data_inicio, data_fim 
        FROM vest_relatorio_fila_execucao 
        WHERE id = ?
        LIMIT 1
    ");

    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            "error" => "102",
            "message" => "Registro não encontrado"
        ]);
        exit;
    }

    $dados = $result->fetch_assoc();

    echo json_encode([
        "error" => "0",
        "message" => "Registro encontrado",
        "data" => [
            "log" => $dados['log'],
            "data_inicio" => $dados['data_inicio'],
            "data_fim" => $dados['data_fim']
        ]
    ]);

    $check->close();

} catch (mysqli_sql_exception $e) {

    error_log($e->getMessage());

    echo json_encode([
        "error" => "100",
        "message" => "Erro ao consultar fila de execução"
    ]);
}
?>