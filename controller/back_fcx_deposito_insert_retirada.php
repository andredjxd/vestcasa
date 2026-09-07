<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$empresa = trim($_POST['empresa'] ?? '');
$dataret = trim($_POST['dataret'] ?? '');
$gvt     = trim($_POST['gvt'] ?? '');
$observ  = trim($_POST['observ'] ?? '');
$id      = (int)($_POST['id'] ?? 0);
$status  = 1; 

function formatarData(?string $data): ?string
{
    if (!$data) return null;

    $dt = DateTime::createFromFormat('d/m/Y', $data);
    return $dt ? $dt->format('Y-m-d') : null;
}

$dataRet = formatarData($dataret);

if ($id === 0) {
    echo json_encode([
        "error" => 102,
        "message" => "ID inválido"
    ]);
    exit;
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $stmt = $cnx->prepare("
        UPDATE vest_relatorio_deposito
        SET 
            gvt     = ?,
            dt_rec  = ?,
            observ  = ?,
            empresa = ?,
            finished = CURRENT_TIMESTAMP(),
            status  = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssssii",
        $gvt,
        $dataRet,
        $observ,
        $empresa,
        $status,
        $id
    );

    $stmt->execute();

    echo json_encode([
        "error"   => 101,
        "message" => "Retirada registrada com sucesso!"
    ]);

} catch (mysqli_sql_exception $e) {

    error_log("Erro vest_relatorio_deposito: " . $e->getMessage());

    echo json_encode([
        "error"   => 102,
        "message" => "Erro ao realizar a atualização."
    ]);
}
