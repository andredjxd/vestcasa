<?php
include '../assets/_db/db.php';
exigirAdminApi();

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_POST['id'] ?? 0);
$email = trim($_POST['email'] ?? '');
$senha = (string) ($_POST['senha'] ?? '');
$role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
$status = (int) ($_POST['status'] ?? 0);

if ($id <= 0 || $email === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Dados invalidos.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ?');
$stmt->execute([$email, $id]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'msg' => 'Ja existe outro usuario com esse e-mail.']);
    exit;
}

if ($senha !== '') {
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE usuarios SET email = ?, senha = ?, role = ?, status = ? WHERE id = ?');
    $stmt->execute([$email, $hash, $role, $status, $id]);
} else {
    $stmt = $pdo->prepare('UPDATE usuarios SET email = ?, role = ?, status = ? WHERE id = ?');
    $stmt->execute([$email, $role, $status, $id]);
}

echo json_encode(['ok' => true]);
