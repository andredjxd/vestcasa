<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$funcao = $_POST['nomefuncao'] ?? '';

if (empty($funcao)) {
    echo json_encode([
        "error" => true,
        "message" => "Função não informada"
    ]);
    exit;
}

$stmt = $cnx->prepare("
    INSERT INTO vest_relatorio_colab_funcao (funcao)
    VALUES (?)
");

if (!$stmt) {
    echo json_encode([
        "error" => true,
        "message" => "Erro ao preparar SQL"
    ]);
    exit;
}

$stmt->bind_param("s", $funcao);

if ($stmt->execute()) {
    echo json_encode([
        "error" => 101,
        "message" => "Cadastro realizado com sucesso",
        "id_insert" => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "error" => true,
        "message" => "Erro ao executar cadastro"
    ]);
}

$stmt->close();
$cnx->close();
