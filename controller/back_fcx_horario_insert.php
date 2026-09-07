<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$hini  = $_POST['hini']  ?? '';
$hfim  = $_POST['hfim']  ?? '';
$inter = $_POST['inter'] ?? '';

// ✅ Validação correta
if (empty($hini) || empty($hfim) || empty($inter)) {
    echo json_encode([
        "error" => true,
        "message" => "Dados obrigatórios não informados"
    ]);
    exit;
}

$stmt = $cnx->prepare("
    INSERT INTO vest_relatorio_colab_horario (hini, hfim, inter)
    VALUES (?, ?, ?)
");

if (!$stmt) {
    echo json_encode([
        "error" => true,
        "message" => "Erro ao preparar SQL"
    ]);
    exit;
}

// ✅ Tipos corretos: string, string, integer
$stmt->bind_param("sss", $hini, $hfim, $inter);

if ($stmt->execute()) {
    echo json_encode([
        "error" => 101,
        "message" => "Cadastro realizado com sucesso",
        "id_insert" => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "error" => true,
        "message" => "Erro ao executar cadastro",
        "sql_error" => $stmt->error
    ]);
}

$stmt->close();
$cnx->close();
