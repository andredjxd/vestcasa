<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

$email = trim($_POST['email'] ?? '');
$senha = (string) ($_POST['senha'] ?? '');

if ($email === '' || $senha === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Informe e-mail e senha.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, senha, role FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'E-mail ou senha invalidos.']);
    exit;
}

session_regenerate_id(true);
$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_email'] = $email;
$_SESSION['usuario_role'] = $usuario['role'];

echo json_encode(['ok' => true]);
