<?php
include '../assets/_db/db.php';
$dados = [];

$query = "
        SELECT id,codigobarra,descricao,preco_clube,limitacao_clube,preco_max,limitacao_max,preco_varejo,limitacao_varejo, updated
        FROM vest_relatorio_produto_precos
";

$res1 = $cnx->query($query);

if ($res1) {
    $pos = 0;
    while ($row = $res1->fetch_assoc()) {
        $pos++;
        $dados[] = [
            "id"              => $row['id']
            ,"codigo"         => $row['codigobarra']
            ,"descricao"      => $row['descricao']
            ,"precoclube"     => $row['preco_clube']
            ,"limiteclube"    => $row['limitacao_clube']
            ,"precomax"       => $row['preco_max']
            ,"limitemax"      => $row['limitacao_max']
            ,"precovarejo"    => $row['preco_varejo']
            ,"limitevarejo"   => $row['limitacao_varejo']
            ,"dataconsulta"   => $row['updated']

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