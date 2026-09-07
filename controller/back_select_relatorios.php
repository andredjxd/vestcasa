<?php
include '../assets/_db/db.php';
$sql = $cnx->query("SELECT * FROM `logman3__validade_relatorio` WHERE status = 0");
while($dad = $sql->fetch_array()){
    $sqlc = $cnx->query("SELECT COUNT(id_rel) AS itens FROM `logman3__validade_relatorio_item` WHERE id_rel = ".$dad['id']."");
    $dads = $sqlc->fetch_array(MYSQLI_BOTH);
    //var_dump($dads);
    $dados[]=array("id"=>$dad['id'],
    "setor"=>$dad['setor'],
    "comprador"=>$dad['comprador'],
    "nome"=>$dad['nome'],
    "data"=>date('d/m/Y',strtotime($dad['updatedata'])),
    "itens"=>$dads['itens']);
}
echo json_encode($dados);