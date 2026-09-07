<?php
include '../assets/_db/db.php';
$id = $_POST['id'];
// $id = 5;
$sql = $cnx->query("SELECT * FROM `logman3__despesa_empresas` WHERE id = '".$id."'");
while($dad = $sql->fetch_array()){
    $dados[]=array(
        "id"=>$dad['id'],
        "cnpj"=>$dad['cnpj'],
        "razao"=>$dad['razao_social']);
        
}
echo json_encode($dados);
