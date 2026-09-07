<?php
include '../assets/_db/db.php';
$id = $_POST['id'];
// $id = 1;
$sql = $cnx->query("SELECT * FROM `logman3__parametros` WHERE id = '".$id."'");
while($dad = $sql->fetch_array()){
    //$database = date_create($dad['data']);
    //$datadehoje = date_create(date('Y-m-d'));
    //$resultado = date_diff($datadehoje, $database);
    //$dias = date_interval_format($resultado, '%r%a');
    //$comprador = substr($dad['comprador'], 0, 20);
    $dados[]=array(
        "id"        =>$dad['id'],
        "pos"       =>$dad['pos'],
        "ope01"     =>$dad['operador01'],
        "dias01"    =>$dad['dias01'],
        "ope02"     =>$dad['operador02'],
        "dias02"    =>$dad['dias02'],
        "setores"   =>$dad['setores']
    );
        
}
echo json_encode($dados);
