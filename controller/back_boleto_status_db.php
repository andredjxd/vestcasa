<?php
include '../assets/_db/db.php';

$SQLDateBoleto = $cnx->query("SELECT datebase FROM logman3__boletos ORDER BY datebase DESC LIMIT 1");
$SQLDateBoletoConsulta = $SQLDateBoleto->fetch_array(MYSQLI_ASSOC);
if(!$SQLDateBoletoConsulta){
    $rDataBoleto = "Tabela Vazia";
}else{
    $rDataBoleto = date('d/m/Y',strtotime($SQLDateBoletoConsulta['datebase']));
}

$SQLUpdateBoleto = $cnx->query("SELECT date_created FROM logman3__boletos ORDER BY date_created DESC LIMIT 1");
$SQLUpdateBoletoConsulta = $SQLUpdateBoleto->fetch_array(MYSQLI_ASSOC);
if(!$SQLUpdateBoletoConsulta){
    $rUpdateBoleto = "Tabela Vazia";
}else{
    $rUpdateBoleto = date('d/m/Y',strtotime($SQLUpdateBoletoConsulta['date_created']));
}

// $sqlQuebra = $cnx->query("SELECT dt_quebra FROM `logman3__quebras_caixa` ORDER BY dt_quebra DESC LIMIT 1;");
// $sqlQuebraConsulta = $sqlQuebra->fetch_array(MYSQLI_ASSOC);
// if(!$sqlQuebraConsulta){
//     $rQuebra = "Tabela Vazia";
// }else{
//     $rQuebra= date('d/m/Y',strtotime($sqlQuebraConsulta['dt_quebra']));
// }

$dados[]=array("dtboleto"=>$rDataBoleto,"upaqruivo"=>$rUpdateBoleto);
echo json_encode($dados);