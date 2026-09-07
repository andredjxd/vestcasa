<?php
include '../assets/_db/db.php';
$ope = $_POST['ope'];
//$ope = 5806;

$contador = 1; // Inicializa o contador

$dado = array();

$consul_quebra = $cnx->query("SELECT qc.operador, op.nome, qc.dt_quebra, qc.valor_qb, qc.valor_pg, qc.resta_pg
                                FROM `logman3__quebras_caixa` as qc
                                LEFT JOIN logman3__operadores AS op ON op.operador = qc.operador
                                WHERE qc.operador = '$ope'
                                ORDER BY qc.dt_quebra ASC;");
// $qtd_ter = $consul_terceiros->fetch_array(MYSQLI_BOTH);
while($qtd_que = $consul_quebra->fetch_array()){
    $dado[]=array(
    "operador"      =>$qtd_que[0]
    ,"nome"         =>$qtd_que[1]
    ,"dt_quebra"    =>$qtd_que[2]
    ,"valor_qb"     =>$qtd_que[3]
    ,"valor_pg"     =>$qtd_que[4]
    ,"resta_pg"     =>$qtd_que[5]
    ,"contador"     =>$contador
    );
    // Incrementa o contador
    $contador++;
}

echo json_encode($dado);
?>