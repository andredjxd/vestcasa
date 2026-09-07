<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$codigo = trim($_POST['codigo'] ?? '');

if (!$codigo) {
    echo json_encode(["erro" => "Código não informado"]);
    exit;
}

$sql = $cnx->prepare("
    INSERT INTO vest_etiquetas_gerador (codigo_barras)
    VALUES (?)
");
$sql->bind_param("s", $codigo);
$sql->execute();

$id = $cnx->insert_id;

$sql->close();
$cnx->close();

echo json_encode(["id" => $id, "codigo" => $codigo]);
