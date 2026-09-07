<?php
include '../assets/_db/db1.php';
$sql = $cnx->query("SELECT * FROM `prevenda` ");
while($dad = $sql->fetch_array()){
    $dados[]=array("id"=>$dad['id']);
}
echo json_encode($dados);