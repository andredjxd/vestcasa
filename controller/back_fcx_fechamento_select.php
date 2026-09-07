<?php
include '../assets/_db/db.php';
$dados = [];

$query = "
    SELECT dt_fecha, sum(saldocartao) as totalpos, sum(valor_deposito) as totaldeposito, sum(valor_sangria) as totalsangria
    FROM vest_relatorio_fechamento 
    GROUP by dt_fecha 
    ORDER BY dt_fecha DESC;
";

$sql = $cnx->query($query);

while ($dad = $sql->fetch_assoc()) {
    $venda = $dad['totalpos'] + $dad['totaldeposito'];
    $dados[] = [
        "data"           => $dad['dt_fecha'],
        "totalpos"       => $dad['totalpos'],
        "totaldeposito"  => $dad['totaldeposito'],
        "totalsangria"   => $dad['totalsangria'],
        "totalgeral"     => $venda
    ];
}


header('Content-Type: application/json');
echo json_encode($dados, JSON_UNESCAPED_UNICODE);