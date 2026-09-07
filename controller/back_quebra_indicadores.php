<?php
include '../assets/_db/db.php';
include '../assets/_db/db.php';

$dados = array();
$consul_quebra = $cnx->query("SELECT 
                                DATE_FORMAT(dt_quebra, '%m/%Y') AS mes_ano, 
                                SUM(resta_pg) AS total_resta_pg
                                FROM logman3__quebras_caixa
                                GROUP BY YEAR(dt_quebra), MONTH(dt_quebra)  -- Agrupa corretamente por ano e mês
                                ORDER BY YEAR(dt_quebra) ASC, MONTH(dt_quebra) ASC;");
// $qtd_ter = $consul_terceiros->fetch_array(MYSQLI_BOTH);
while($qtd_que = $consul_quebra->fetch_array()){
    $dados[]=array(
    "mesano"           =>$qtd_que[0]
    ,"totalrestapg"   =>$qtd_que[1]
    
    );
}

echo json_encode($dados);