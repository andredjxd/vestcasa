<?php
include '../assets/_db/db.php';
$dados = array();
$nome = $_POST['name'];
// $nome = 1688;

$sql = "SELECT data FROM `logman3__srtbi03` GROUP BY data ORDER BY data DESC LIMIT 1;";
$result = $cnx->query($sql);

$row = $result->fetch_assoc();
$data = $row ? $row['data'] : 'Nenhum resultado';

$sql = $cnx->query("SELECT smg.codigo,smg.sub_cod,smg.descricao,smg.emb,smg.comprador,smg.rent AS arent,smg.qtd_venda_emb1,smg.est_emb1,smg.est_emb9,
                    srt.emb1,srt.emb9,srt.valor_venda,srt.variacao,srt.rent AS rrent,srt.avaria,srt.ajuste,srt.resultado
                    FROM logman3__smgoi13 AS smg 
                    LEFT JOIN logman3__srtbi03 AS  srt ON  srt.codigo = smg.codigo
                    WHERE (smg.codigo LIKE '".$nome."%' OR smg.descricao LIKE '".$nome."%') AND srt.data = '$data'");
while($dad = $sql->fetch_array(MYSQLI_BOTH)){
    if($dad['emb1'] == null and $dad['emb9'] == null){
        $venda = 'S/Venda';
    }else{
        $venda = 'C:'.$dad['emb1'].' U:'.$dad['emb9'];
    }
    $dados[]=array("value"=>$dad['codigo'].'|'.$dad['descricao'].'|'.$dad['comprador'],
        "sub"=>$dad['sub_cod'],
        "emba"=>$dad['emb'],
        "comp"=>$dad['comprador'],
        "arent"=>$dad['arent'],
        "vnda"=>$dad['qtd_venda_emb1'],
        "venda"=>$venda,
        "estoq"=>'C:'.$dad['est_emb1'].' U:'.$dad['est_emb9'],
        "vvnd"=>''.$dad['valor_venda'],
        "rrent"=>''.$dad['rrent'],
        "avar"=>''.$dad['avaria'],
        "ajus"=>''.$dad['ajuste'],
        "resu"=>''.$dad['variacao'],);
}
echo json_encode($dados);
?>