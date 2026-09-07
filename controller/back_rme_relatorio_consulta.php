<?php
include '../assets/_db/db.php';
$dados = [];

$query = "
        SELECT
            id,
            COUNT(id) as qtd,
            loja,
            cnpj,
            status,
            chamado,
            data_recebimento
        FROM vest_rme_recebimento
        GROUP BY chamado  
        ORDER BY status ASC;
";

$res1 = $cnx->query($query);

if ($res1) {
    $pos = 0;
    while ($row = $res1->fetch_assoc()) {
        $pos++;
        $dados[] = [
            "id"               => $row['id']
            ,"pos"             => $pos
            ,"qtd"             => $row['qtd']
            ,"loja"            => $row['loja']
            ,"cnpj"            => $row['cnpj']
            ,"status"          => $row['status']
            ,"chamado"         => $row['chamado']
            ,"datarecebimento" => $row['data_recebimento']
        ];
        
    }
} else {
    $dados = [
        "error" => "103",
        "message" => "Erro ao executar consulta: " . $cnx->error
    ];
}

header('Content-Type: application/json');
echo json_encode($dados, JSON_UNESCAPED_UNICODE);