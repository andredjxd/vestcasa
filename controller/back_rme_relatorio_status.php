<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$dados = [
    "aguardando" => 0,
    "conferido" => 0,
    "finalizado" => 0
];

$query = "
    SELECT status, COUNT(DISTINCT chamado) AS total
    FROM vest_rme_recebimento
    WHERE status IN (0,2,3)
    GROUP BY status
";

$res = $cnx->query($query);

if ($res) {

    while ($row = $res->fetch_assoc()) {

        switch ($row['status']) {
            case 0:
                $dados['aguardando'] = (int)$row['total'];
                break;
            case 2:
                $dados['conferido'] = (int)$row['total'];
                break;
            case 3:
                $dados['finalizado'] = (int)$row['total'];
                break;
        }
    }

    echo json_encode($dados, JSON_UNESCAPED_UNICODE);

} else {
    echo json_encode([
        "error" => "103",
        "message" => "Erro ao executar consulta: " . $cnx->error
    ]);
}