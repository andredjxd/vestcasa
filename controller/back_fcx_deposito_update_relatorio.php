<?php
include '../assets/_db/db.php';
header('Content-Type: application/json');

// ==============================
// RECEBER DADOS
// ==============================
$id          = $_POST['id'] ?? null;
$loja        = $_POST['loja'] ?? null;
$datainicial = $_POST['datainicial'] ?? null;
$datafinal   = $_POST['datafinal'] ?? null;

// ==============================
// VALIDAÇÃO
// ==============================
if (
    empty($id) ||
    empty($loja) ||
    empty($datainicial) ||
    empty($datafinal)
) {
    echo json_encode([
        "error" => 102,
        "message" => "Dados incompletos"
    ]);
    exit;
}

// ==============================
// FORMATAR DATA (dd/mm/yyyy → yyyy-mm-dd)
// ==============================
function formatarData($data) {
    if (!$data) return null;

    $dt = DateTime::createFromFormat('d/m/Y', $data);
    return $dt ? $dt->format('Y-m-d') : null;
}

$datainicial = formatarData($datainicial);
$datafinal   = formatarData($datafinal);

// ==============================
// VALIDAR DATAS
// ==============================
if (!$datainicial || !$datafinal) {
    echo json_encode([
        "error" => 104,
        "message" => "Formato de data inválido"
    ]);
    exit;
}

if ($datainicial > $datafinal) {
    echo json_encode([
        "error" => 105,
        "message" => "Data inicial não pode ser maior que a final"
    ]);
    exit;
}

// ==============================
// UPDATE MYSQLI
// ==============================
try {

    $sql = "UPDATE vest_relatorio_deposito 
            SET loja = ?, 
                dt_ini = ?, 
                dt_fim = ?
            WHERE id = ?";

    $stmt = $cnx->prepare($sql);

    if (!$stmt) {
        throw new Exception($cnx->error);
    }

    $stmt->bind_param(
        "ssss", // todos como string
        $loja,
        $datainicial,
        $datafinal,
        $id
    );

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    echo json_encode([
        "error" => 101,
        "message" => "Relatório atualizado com sucesso!"
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "error" => 100,
        "message" => "Erro ao atualizar: " . $e->getMessage()
    ]);
    exit;
}