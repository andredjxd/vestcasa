<?php
include '../assets/_db/db.php';
$ini = $_POST['ini'];
// $fim = $_POST['fim'];
// $ini = 20240401;
// $fim = 20240430;
$dados = array();
$sql = $cnx->query("SELECT * FROM `logman3__cafeteria_metas` WHERE data = '$ini' ORDER BY data ASC");
while($dad = $sql->fetch_array()){
    // $database = date_create($dad['data']);
    // $datadehoje = date_create(date('Y-m-d'));
    // $resultado = date_diff($datadehoje, $database);
    // $dias = date_interval_format($resultado, '%r%a');
    // $comprador = substr($dad['comprador'], 0, 20);
    $dados[]=array(
        "data"=>date('d/m/Y',strtotime($dad['data'])),
        "datames"=>date('m/Y',strtotime($dad['data'])),
        "taxa"=>$dad['taxa_adm'],
        "metavenda"=>$dad['venda_meta'],
        "metarent"=>$dad['rent_meta'],
        "despessoal"=>$dad['desp_pessoal'],
        "desgeral"=>$dad['desp_geral']);
        
}
echo json_encode($dados);
