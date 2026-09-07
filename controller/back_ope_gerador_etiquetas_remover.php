<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$id = (int) ($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode(["erro" => "ID não informado"]);
    exit;
}

$sql = $cnx->prepare("DELETE FROM vest_etiquetas_gerador WHERE id = ?");
$sql->bind_param("i", $id);
$sql->execute();
$sql->close();
$cnx->close();

echo json_encode(["ok" => true]);
