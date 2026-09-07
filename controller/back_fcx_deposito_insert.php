<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$dados = $_POST['dados'] ?? [];

$idrel           = $dados['idrel']           ?? null;
$datadeposito    = $dados['datadeposito']    ?? null;
$pdv             = $dados['pdv']             ?? null;
$nomeoperador    = $dados['nomeoperador']    ?? null;
$nomelideranca   = $dados['nomelideranca']   ?? null;
$valodeposito    = $dados['valodeposito']    ?? null;
$numerobananinha = $dados['numerobananinha'] ?? null;

function formatarData(?string $data): ?string {
    if (!$data) return null;
    $dt = DateTime::createFromFormat('d/m/Y', $data);
    return $dt ? $dt->format('Y-m-d') : null;
}

$datadep = formatarData($datadeposito);

/* 🔒 Validação mínima */
if (
    !$idrel || !$datadep || !$pdv || !$nomeoperador ||
    !$nomelideranca || !$valodeposito || !$numerobananinha
) {
    echo json_encode([
        "error" => "102",
        "message" => "CAMPOS OBRIGRATÓRIO AUSENTES!!!"
    ]);
    exit;
}

try {
    $stmt = $cnx->prepare("
        INSERT INTO vest_relatorio_deposito_itens
        (idrel, data, pdv, operador, lideranca, valor, numbanana)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isissdi",
        $idrel,
        $datadep,
        $pdv,
        $nomeoperador,
        $nomelideranca,
        $valodeposito,
        $numerobananinha
    );

    $stmt->execute();

    echo json_encode([
        "error" => "101",
        "message" => "Cadastro realizado com sucesso",
        "id_insert" => $stmt->insert_id
    ]);

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());

    echo json_encode([
        "error" => "100",
        "message" => "Erro ao salvar no banco de dados"
    ]);
}
?>