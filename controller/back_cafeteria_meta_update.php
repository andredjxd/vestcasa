<?php
include '../assets/_db/db.php';
$taxa = $_POST['taxa'];
$dpessoal = $_POST['dpessoal'];
$dgeral = $_POST['dgeral'];
$metavenda = $_POST['metavenda'];
$metarent = $_POST['metarent'];
$data = date('Ydm',strtotime($_POST['data']));

// $ini = 20240401;
// $fim = 20240430;
$dados = array();
$sql = $cnx->query("SELECT * FROM `logman3__cafeteria_metas` WHERE data = '$data' ORDER BY data ASC");
if(mysqli_num_rows($sql) !=0){
    $dados[]=array("data"=>"TEM","datas"=>$data);
    $sqlUpdate = "UPDATE `logman3__cafeteria_metas` SET `taxa_adm`='$taxa',`desp_pessoal`='$dpessoal',`desp_geral`='$dgeral',`venda_meta`='$metavenda',`rent_meta`='$metarent' WHERE data = '$data'";
	$exePeso = $cnx->query($sqlUpdate);
    if (!$exePeso) {
        echo 100;; 
    }
}else{
    $dados[]=array("data"=>"NAO TEM","datas"=>$data);
    $sqlInsert = "INSERT INTO `logman3__cafeteria_metas` (`data`,`taxa_adm`, `desp_pessoal`, `desp_geral`, `venda_meta`, `rent_meta`) VALUES ('$data','$taxa','$dpessoal','$dgeral','$metavenda','$metarent')";
	$exeInsert = $cnx->query($sqlInsert);
    if (!$exeInsert) {
        echo 100;; 
    }
}
// while($dad = $sql->fetch_array()){
//     // $database = date_create($dad['data']);
//     // $datadehoje = date_create(date('Y-m-d'));
//     // $resultado = date_diff($datadehoje, $database);
//     // $dias = date_interval_format($resultado, '%r%a');
//     // $comprador = substr($dad['comprador'], 0, 20);
//     $dados[]=array(
//         "data"=>date('d/m/Y',strtotime($dad['data'])),
//         "taxa"=>$dad['taxa_adm'],
//         // "grupono"=>$dad['grupoNome'],
//         "despessoal"=>$dad['desp_pessoal'],
//         "desgeral"=>$dad['desp_geral']);
        
// }
// echo json_encode($dados);
