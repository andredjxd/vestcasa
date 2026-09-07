<?php
include '../assets/_db/db.php';
$dados = array();
$sql = $cnx->query("SELECT `id`, `cnpj`, `razao_social` FROM `logman3__despesa_empresas`");
while($dad = $sql->fetch_array()){
    $dados[]=array(
        "id"=>$dad['id'],
        "cnpj"=>$dad['cnpj'],
        "razao"=>$dad['razao_social']);
}
echo json_encode($dados);
