<?php
include '../assets/_db/db.php';
exigirAdminApi();

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Dados invalidos.']);
    exit;
}

$stmt = $pdo->prepare('DELETE FROM perfis WHERE id = ?');
$stmt->execute([$id]);

echo json_encode(['ok' => true]);
