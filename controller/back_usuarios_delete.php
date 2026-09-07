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

if ($id === (int) ($_SESSION['usuario_id'] ?? 0)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Voce nao pode excluir o proprio usuario.']);
    exit;
}

$stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
$stmt->execute([$id]);

echo json_encode(['ok' => true]);
