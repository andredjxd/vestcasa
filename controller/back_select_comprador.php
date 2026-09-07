<?php
include '../assets/_db/db.php';
$dados = array();
$sql = $cnx->query("SELECT comprador FROM `logman3__smgoi13` GROUP BY comprador");
while($dad = $sql->fetch_array()){
    $dados[]=array("comprador"=>$dad['comprador']);
}
echo json_encode($dados);