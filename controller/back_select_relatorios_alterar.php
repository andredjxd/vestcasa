<?php
include '../assets/_db/db.php';
$id = $_POST['id'];
$sql = $cnx->query("SELECT * FROM `logman3__validade_relatorio` WHERE status = 0 AND id = ".$id."");
while($dad = $sql->fetch_array()){
    //var_dump($dads);
    $dados[]=array("id"=>$dad['id'],
    "setor"=>$dad['setor'],
    "comprador"=>$dad['comprador'],
    "nome"=>$dad['nome']);
}
echo json_encode($dados);