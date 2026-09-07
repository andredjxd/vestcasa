<?php
include '../assets/_db/db.php';
$dados = [];

$query = "
        SELECT * FROM `vest_relatorio_colab_funcao`
";

$res1 = $cnx->query($query);

if ($res1) {
    $pos = 0;
    while ($row = $res1->fetch_assoc()) {
        $pos++;
        $dados[] = [
            "id"           => $row['id']
            ,"funcao"      => $row['funcao']              
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