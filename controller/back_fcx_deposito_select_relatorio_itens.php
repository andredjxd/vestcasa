<?php
include '../assets/_db/db.php';
$idrel = $_POST['id'];
// $idrel = 14;
$dados = [];

$query = "
SELECT vrdi.id, vrdi.idrel, vrdi.data, vrdi.pdv, vrc.nome, vrdi.lideranca, vrdi.valor, vrdi.numbanana, vrdi.created 
FROM vest_relatorio_deposito_itens AS vrdi
LEFT JOIN vest_relatorio_colaborador AS vrc ON vrdi.operador = vrc.id
WHERE vrdi.idrel = $idrel
ORDER BY vrdi.data DESC;
";

$res1 = $cnx->query($query);

if ($res1) {
    $pos = 0;
    while ($row = $res1->fetch_assoc()) {
        $pos++;
        $dados[] = [
            "pos"           => $pos
            ,"id"           => $row['id']
            ,"idrel"        => $row['idrel']
            ,"data"         => $row['data']
            ,"pdv"          => $row['pdv']
            ,"operador"     => $row['nome']
            ,"lideranca"    => $row['lideranca']
            ,"valor"        => $row['valor']
            ,"numbanana"    => $row['numbanana']
            ,"created"      => $row['created']
                           
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