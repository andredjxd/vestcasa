<?php
include '../assets/_db/db.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
// $id = 10;

$dados = [];

if (!$id) {
    echo json_encode($dados);
    exit;
}

$query = "
    SELECT * 
    FROM vest_relatorio_fechamento 
    WHERE id = ?;
";

$stmt = $cnx->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

while ($dad = $result->fetch_assoc()) {

    $dados[] = [
        "id"             => $dad['id'],
        "data"           => $dad['dt_fecha'],
        "operador"       => $dad['operador'],
        "pdv"            => $dad['pdv'],
        "saldoini"       => (float)$dad['saldoini'],
        "saldocartao"    => (float)$dad['saldocartao'],
        "pos"            => $dad['pos'],
        "deposito"       => (float)$dad['valor_deposito'],
        "sangria"        => (float)$dad['valor_sangria'],
        "saldofinal"     => (float)$dad['saldofinal'],
        "quebra"         => (float)$dad['quebra'],
        "trocafundo"     => (float)$dad['trocafundo'],
        "observ"         => $dad['observ']
    ];
}

$stmt->close();
$cnx->close();

echo json_encode($dados, JSON_UNESCAPED_UNICODE);
