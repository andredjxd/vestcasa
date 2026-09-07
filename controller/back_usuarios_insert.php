<?php
include '../assets/_db/db.php';
exigirAdminApi();

header('Content-Type: application/json; charset=utf-8');

$email = trim($_POST['email'] ?? '');
$senha = (string) ($_POST['senha'] ?? '');
$role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
$status = (int) ($_POST['status'] ?? 0);

if ($email === '' || $senha === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Informe e-mail e senha.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'msg' => 'Ja existe um usuario com esse e-mail.']);
    exit;
}

$hash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('INSERT INTO usuarios (email, senha, role, status) VALUES (?, ?, ?, ?)');
$stmt->execute([$email, $hash, $role, $status]);

echo json_encode(['ok' => true]);
