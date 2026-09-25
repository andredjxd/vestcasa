<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$tipo = mb_strtoupper(trim($_POST['tipo'] ?? ''), 'UTF-8');

if ($tipo === '') {
    echo json_encode(["error" => true, "message" => "Informe o nome do tipo de avaria."]);
    exit;
}

try {
    $stmtDup = $cnx->prepare("SELECT id FROM vest_avaria_tipo_avaria WHERE tipo = ? LIMIT 1");
    $stmtDup->bind_param("s", $tipo);
    $stmtDup->execute();

    if ($stmtDup->get_result()->num_rows > 0) {
        throw new Exception("Este tipo de avaria já existe.");
    }

    $stmt = $cnx->prepare("INSERT INTO vest_avaria_tipo_avaria (tipo) VALUES (?)");
    $stmt->bind_param("s", $tipo);
    $stmt->execute();

    echo json_encode([
        "error" => false,
        "message" => "Tipo de avaria adicionado!",
        "id" => $cnx->insert_id
    ]);

} catch (Throwable $e) {
    echo json_encode(["error" => true, "message" => $e->getMessage()]);
}
