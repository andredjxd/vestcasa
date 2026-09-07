<?php
include '../assets/_db/db.php';
exigirAdminApi();

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_POST['id'] ?? 0);
$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$acessoWeb = !empty($_POST['acesso_web']) ? 1 : 0;
$acessoApp = !empty($_POST['acesso_app']) ? 1 : 0;
$isAdmin = !empty($_POST['is_admin']) ? 1 : 0;

if ($id <= 0 || $nome === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Dados invalidos.']);
    exit;
}

$stmt = $pdo->prepare('UPDATE perfis SET nome = ?, descricao = ?, acesso_web = ?, acesso_app = ?, is_admin = ? WHERE id = ?');
$stmt->execute([$nome, $descricao, $acessoWeb, $acessoApp, $isAdmin, $id]);

echo json_encode(['ok' => true]);
