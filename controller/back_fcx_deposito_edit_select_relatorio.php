<?php
include '../assets/_db/db.php';
$ids = $_POST['id'];
// $ids = 18;
$dados = [];

$query = "SELECT * , SUM(vrdi.valor) AS total, COUNT(vrdi.valor) AS qtd
                        FROM `vest_relatorio_deposito_itens` AS vrdi
                        INNER JOIN vest_relatorio_deposito AS vrd ON vrd.id = vrdi.idrel
                        WHERE vrdi.idrel = ?";
$stmt = $cnx->prepare($query);
$stmt->bind_param("i", $ids);
$stmt->execute();
$result = $stmt->get_result();
$dad = $result->fetch_assoc();

if ($dad['status'] == 1 ){
    $status = "Finalizado";
}else{
    $status = "Em Aberto";
}

$dados[] = [
    "id"       => $dad['id']
    ,"loja"     => $dad['loja']
    ,"dtIni"    => $dad['dt_ini']
    ,"dtFim"    => $dad['dt_fim']
    ,"valor"    => $dad['total']
    ,"status"   => $status
    ,"qtd"      => $dad['qtd']
    ,"empresa"  => $dad['empresa']
    ,"gvt"      => $dad['gvt']
    ,"dtRec"    => $dad['dt_rec']
    ,"observ"   => $dad['observ']
    ,"created"  => $dad['created']
    ,"finished"  => $dad['finished']
                    
];


header('Content-Type: application/json');
echo json_encode($dados, JSON_UNESCAPED_UNICODE);