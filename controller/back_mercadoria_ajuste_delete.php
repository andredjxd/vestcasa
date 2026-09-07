<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode([
        "status" => "error",
        "message" => "Item invalido"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $cnx->prepare("
    UPDATE vest_mercadoria_ajuste
    SET status = 3, updated_at = NOW()
    WHERE id = ? AND status = 1
");
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Item nao encontrado ou ja ajustado"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Item removido da lista de ajuste"
], JSON_UNESCAPED_UNICODE);
