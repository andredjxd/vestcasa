<?php
include '../assets/_db/db.php';

$SQLoperador = $cnx->query("SELECT data FROM `logman3__operadores` ORDER BY data DESC LIMIT 1");
$sqlOperadorConsulta = $SQLoperador->fetch_array(MYSQLI_ASSOC);
if(!$sqlOperadorConsulta){
    $rOperador = "Tabela Vazia";
}else{
    $rOperador = date('d/m/Y',strtotime($sqlOperadorConsulta['data']));
}

$sqlQuebra = $cnx->query("SELECT dt_quebra FROM `logman3__quebras_caixa` ORDER BY dt_quebra DESC LIMIT 1;");
$sqlQuebraConsulta = $sqlQuebra->fetch_array(MYSQLI_ASSOC);
if(!$sqlQuebraConsulta){
    $rQuebra = "Tabela Vazia";
}else{
    $rQuebra= date('d/m/Y',strtotime($sqlQuebraConsulta['dt_quebra']));
}

$sqlQuebraCreate = $cnx->query("SELECT created_at FROM `logman3__quebras_caixa` LIMIT 1;");
$sqlQuebraConsultaCreate = $sqlQuebraCreate->fetch_array(MYSQLI_ASSOC);
if(!$sqlQuebraConsultaCreate){
    $rQuebraCreate = "Tabela Vazia";
}else{
    $rQuebraCreate= date('d/m/Y',strtotime($sqlQuebraConsultaCreate['created_at']));
}

$dados[]=array("dtoperador"=>$rOperador,"dtquebra"=>$rQuebra,"dtcreate"=>$rQuebraCreate);
echo json_encode($dados);