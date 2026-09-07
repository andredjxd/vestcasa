<?php
include '../assets/_db/db.php';

$SQLTitulosOpen = $cnx->query("SELECT SUM(valor) AS total FROM `logman3__boletos` WHERE pagamento IS NULL;");
$SQLTitulosOpenConsulta = $SQLTitulosOpen->fetch_array(MYSQLI_ASSOC);
if(!$SQLTitulosOpenConsulta){
    $rTitulosOpen = "Sem Dados";
}else{
    $rTitulosOpen = $SQLTitulosOpenConsulta['total'];
}

$SQLTitulosVenci = $cnx->query("SELECT SUM(valor) totalvenci FROM `logman3__boletos` WHERE vencimento < CURDATE() AND pagamento IS NULL;");
$SQLTitulosVenciConsulta = $SQLTitulosVenci->fetch_array(MYSQLI_ASSOC);
if(!$SQLTitulosVenciConsulta){
    $rTitulosVenci = "Sem Dados";
}else{
    $rTitulosVenci = $SQLTitulosVenciConsulta['totalvenci'];
}

$dados[]=array("titulopen"=>$rTitulosOpen,"titulovenci"=>$rTitulosVenci);

echo json_encode($dados);