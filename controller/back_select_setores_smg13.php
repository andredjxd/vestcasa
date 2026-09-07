<?php
include '../assets/_db/db.php';
$dados = array();
$sql = $cnx->query("SELECT setor_num, setor_nom FROM `logman3__setores` ORDER BY setor_num ASC");
while($dad = $sql->fetch_array()){
    $dados[]=array("snu"=>$dad['setor_num'],"sno"=>$dad['setor_nom']);
}
echo json_encode($dados);