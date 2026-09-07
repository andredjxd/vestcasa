<?php
include '../assets/_db/db.php';
$sql = $cnx->query("SELECT * FROM `logman3__srtbi03` ORDER BY `resul_ll` ASC limit 100");
while($dad = $sql->fetch_array()){
    // $sqlc = $cnx->query("SELECT COUNT(id_rel) AS itens FROM `logman3__validade_relatorio_item` WHERE id_rel = ".$dad['id']."");
    //$dads = $sqlc->fetch_array(MYSQLI_BOTH);
    //var_dump($dads);
    //$resultado = $dad['leitura23'] - $dad['leitura06'];
    $dados[]=array("codigo"=>$dad['codigo'],
    "sub"=>$dad['sub'],
    "data"=>date('d/m/Y',strtotime($dad['data'])),
    "descricao"=>$dad['descricao'],
    "emb"=>$dad['emb'],
    "emb1"=>$dad['emb1'],
    "emb9"=>$dad['emb9'],
    "valvnd"=>$dad['valor_venda'],
    "variacao"=>$dad['variacao'],
    "rent"=>$dad['rent'],
    "ajuste"=>$dad['ajuste'],
    "avaria"=>$dad['avaria'],
    "resultado"=>$dad['resultado'],
    "resulll"=>$dad['resul_ll']);
}
echo json_encode($dados);