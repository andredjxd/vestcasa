<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id   = (int) ($_POST['id'] ?? 0);
$tipo = mb_strtoupper(trim($_POST['tipo'] ?? ''), 'UTF-8');

if ($id <= 0) {
    echo json_encode(["error" => true, "message" => "ID do tipo não informado."]);
    exit;
}

if ($tipo === '') {
    echo json_encode(["error" => true, "message" => "Informe o nome do tipo de avaria."]);
    exit;
}

try {
    $stmtDup = $cnx->prepare("SELECT id FROM vest_avaria_tipo_avaria WHERE tipo = ? AND id <> ? LIMIT 1");
    $stmtDup->bind_param("si", $tipo, $id);
    $stmtDup->execute();

    if ($stmtDup->get_result()->num_rows > 0) {
        throw new Exception("Já existe outro tipo de avaria com este nome.");
    }

    $stmt = $cnx->prepare("UPDATE vest_avaria_tipo_avaria SET tipo = ? WHERE id = ?");
    $stmt->bind_param("si", $tipo, $id);
    $stmt->execute();

    echo json_encode(["error" => false, "message" => "Tipo de avaria atualizado!"]);

} catch (Throwable $e) {
    echo json_encode(["error" => true, "message" => $e->getMessage()]);
}
