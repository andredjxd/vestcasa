<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$dados = $_POST['dadofecha'] ?? [];

$datafecha      = $dados['datafecha']     ?? null;
$operador       = $dados['operador']      ?? null;
$numpdv         = $dados['numpdv']        ?? null;
$saldoinicial   = $dados['saldoinicial']  ?? null;
$saldopos       = $dados['saldopos']      ?? null;
$numpos         = $dados['numpos']        ?? null;
$saldofinal     = $dados['saldofinal']    ?? null;
$fechadeposito  = $dados['fechadeposito'] ?? null;
$fechasangria   = $dados['fechasangria']  ?? null;
$fechaquebra    = $dados['fechaquebra']   ?? null;
$fechafundo     = $dados['fechafundo']    ?? null;
$fechaobvserv   = $dados['fechaobvserv']  ?? null;

function formatarData(?string $data): ?string {
    if (!$data) return null;
    $dt = DateTime::createFromFormat('d/m/Y', $data);
    return $dt ? $dt->format('Y-m-d') : null;
}

$dataFC = formatarData($datafecha);

/* 🔒 Validação mínima */
if (
    !$datafecha || !$operador || !$numpdv || !$saldoinicial ||
    !$saldofinal || !$numpos
) {
    echo json_encode([
        "error" => "102",
        "message" => "CAMPOS OBRIGRATÓRIO AUSENTES!!!"
    ]);
    exit;
}

try {
    $stmt = $cnx->prepare("
        INSERT INTO vest_relatorio_fechamento
        (dt_fecha, operador, pdv, saldoini, saldocartao, saldofinal, pos, valor_deposito, valor_sangria, quebra, trocafundo, observ)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");


    $stmt->bind_param(
        "ssiddiidddds",
        $dataFC,         // s (date vira string)
        $operador,       // s
        $numpdv,         // i
        $saldoinicial,   // d
        $saldopos,       // d
        $saldofinal,     // d
        $numpos,         // i
        $fechadeposito,  // d
        $fechasangria,   // d
        $fechaquebra,    // d
        $fechafundo,     // d
        $fechaobvserv    // s
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