<?php
include '../assets/_db/db.php';
exigirAdminApi();

$dados = array();
$sql = $cnx->query("SELECT id, nome, descricao, acesso_web, acesso_app, is_admin, created FROM perfis ORDER BY nome ASC");
while ($row = $sql->fetch_assoc()) {
    $dados[] = $row;
}

header('Content-Type: application/json');
echo json_encode($dados);
