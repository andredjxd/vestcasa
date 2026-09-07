<?php
include '../assets/_db/db.php';
header('Content-Type: application/json');

$data = $_POST['data'] ?? null;
// $data = '2026-02-10';

$dados = [];

if (!$data) {
    echo json_encode($dados);
    exit;
}

$query = "
    SELECT 
        vrf.id,
        vrf.dt_fecha,
        vrc.nome,
        vrf.pdv,
        vrf.saldoini,
        vrf.saldocartao,
        vrf.pos,
        vrf.valor_deposito,
        vrf.valor_sangria,
        vrf.saldofinal,
        vrf.observ
    FROM vest_relatorio_fechamento AS vrf
    LEFT JOIN vest_relatorio_colaborador AS vrc 
        ON vrf.operador = vrc.id
    WHERE vrf.dt_fecha = ?
    ORDER BY vrf.dt_fecha DESC
";

$stmt = $cnx->prepare($query);
$stmt->bind_param("s", $data);
$stmt->execute();
$result = $stmt->get_result();

while ($dad = $result->fetch_assoc()) {

    $diferenca = (float)$dad['saldoini'] - (float)$dad['saldofinal'];
    $totalgeral = (float)$dad['valor_deposito'] + (float)$dad['saldocartao'] + (float)$dad['valor_sangria'] - $diferenca;

    $dados[] = [
        "idfcx"          => $dad['id'],
        "data"           => $dad['dt_fecha'],
        "operador"       => $dad['nome'],
        "pdv"            => $dad['pdv'],
        "saldoini"       => (float)$dad['saldoini'],
        "saldocartao"    => (float)$dad['saldocartao'],
        "deposito"       => (float)$dad['valor_deposito'],
        "sangria"        => (float)$dad['valor_sangria'],
        "saldofinal"     => (float)$dad['saldofinal'],
        "totalgeral"     => $totalgeral,
        "observ"         => $dad['observ']
    ];
}

$stmt->close();
$cnx->close();

echo json_encode($dados, JSON_UNESCAPED_UNICODE);
