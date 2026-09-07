<?php
include '../assets/_db/db.php';
$idreal = $_POST['idre'];
$codigo = $_POST['codig'];
$subcod = $_POST['sub'];
$descri = $_POST['descricao'];
$embala = $_POST['emb'];
$compra = $_POST['comprado'];
//$datavc = $_POST['data'];
$datavc = date('Y-m-d H:i:s', strtotime(str_replace("/","-",$_POST['data'])));
$rentre = $_POST['rentr'];
$vndrea = $_POST['vndReal'];
$rentat = $_POST['renta'];
$vnda30 = $_POST['vnd30'];
$estoqu = $_POST['estoque'];
$caixas = $_POST['cxa'];
$unidad = $_POST['und'];
$result = $_POST['resultado'];

$dados[]=array("codigo"=>$codigo,"sub"=>$subcod,"descricao"=>$descri,"emb"=>$embala,"comprado"=>$compra,"datavc"=>$datavc,"cxa"=>$caixas,"und"=>$unidad);

$sqlInsert = "INSERT INTO `logman3__validade_relatorio_item`(`id_rel`, `codigo`, `sub`, `descricao`, `emb`, `comprador`, `data`, `rentrea`, `vndreal`, `rentatu`, `vnd30ds`, `estoque`, `cxa`, `und`, `resultado`) 
                VALUES ('$idreal','$codigo','$subcod','$descri','$embala','$compra','$datavc','$rentre','$vndrea','$rentat','$vnda30','$estoqu','$caixas','$unidad','$result')";
$exe = $cnx->query($sqlInsert);
echo json_encode($dados);
?>