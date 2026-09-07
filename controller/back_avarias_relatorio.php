<?php
include '../assets/_db/db.php';
$dados = [];

$query = "
        SELECT
            r.id, r.codigo_avaria, r.situacao, r.descricao, r.empresa, r.quantidade_produto, r.data_criacao,
            COALESCE((
                SELECT SUM(i.preco_contrapartida * i.quantidade)
                FROM vest_avarias_relatorio_itens i
                WHERE i.codigo_avaria = r.codigo_avaria
            ), 0) AS total_valor
        FROM vest_avarias_relatorio r
        ORDER BY r.codigo_avaria ASC;
";

$res1 = $cnx->query($query);

if ($res1) {
    $pos = 0;
    while ($row = $res1->fetch_assoc()) {
        $pos++;
        $dados[] = [
            "id"               => $row['id']
            ,"pos"             => $pos
            ,"codigoavaria"    => $row['codigo_avaria']
            ,"empresa"         => $row['empresa']
            ,"descricao"       => $row['descricao']
            ,"situacao"        => $row['situacao']
            ,"quantidade"      => $row['quantidade_produto']
            ,"datacriacao"     => $row['data_criacao']
            ,"totalvalor"      => $row['total_valor']
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