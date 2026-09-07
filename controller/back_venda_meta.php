<?php
include '../assets/_db/db.php';

// $ini = '20260201';

$ini = $_POST['ini'];
$dados = array();
$sql = $cnx->query("SELECT * FROM `vest_relatorio_vendas_meta` WHERE data = '$ini' ORDER BY data ASC");
while($dad = $sql->fetch_array()){

    $dados[]=array(
        "data"=>date('d/m/Y',strtotime($dad['data'])),
        "datames"=>date('m/Y',strtotime($dad['data'])),
        "metavenda"=>$dad['venda_meta']
    );
        
}
header('Content-Type: application/json');
echo json_encode($dados);

