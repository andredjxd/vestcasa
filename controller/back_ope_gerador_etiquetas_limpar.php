<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$cnx->query("TRUNCATE TABLE vest_etiquetas_gerador");
$cnx->close();

echo json_encode(["ok" => true]);
