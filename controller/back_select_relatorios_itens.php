<?php
include '../assets/_db/db.php';
$id = $_POST['id'];
//$id = 3;
$sql = $cnx->query("SELECT * FROM `logman3__validade_relatorio_item` WHERE id_rel = '".$id."'");
while($dad = $sql->fetch_array()){
    $database = date_create($dad['data']);
    $datadehoje = date_create(date('Y-m-d'));
    $resultado = date_diff($datadehoje, $database);
    $dias = date_interval_format($resultado, '%r%a');
    $comprador = substr($dad['comprador'], 0, 20);
    $dados[]=array(
        "idrel"     =>$dad['id_rel'],
        "id"        =>$dad['id'],
        "codigo"    =>$dad['codigo'],
        "sub"       =>$dad['sub'],
        "desc"      =>$dad['descricao'],
        "emba"      =>$dad['emb'],
        "comp"      =>$comprador,
        "data"      =>date('d/m/y',strtotime($dad['data'])),
        "dias"      =>$dias,
        "rentrea"   =>$dad['rentrea'],
        "vndreal"   =>$dad['vndreal'],
        "rentau"    =>$dad['rentatu'],
        "vnd30"     =>$dad['vnd30ds'],
        "estoque"   =>$dad['estoque'],
        "cxa"       =>$dad['cxa'],
        "und"       =>$dad['und'],
        "resu"      =>$dad['resultado']);
        
}
echo json_encode($dados);
