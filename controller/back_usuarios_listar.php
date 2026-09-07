<?php
include '../assets/_db/db.php';
exigirAdminApi();

$dados = array();
$sql = $cnx->query("SELECT id, email, role, status, foto, created_at FROM usuarios ORDER BY email ASC");
while ($row = $sql->fetch_assoc()) {
    $dados[] = $row;
}

header('Content-Type: application/json');
echo json_encode($dados);
