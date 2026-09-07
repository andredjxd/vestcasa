<?php
include '../assets/_db/db.php';

$dados = array();
$consul_quebra = $cnx->query("SELECT DATE_FORMAT(vencimento, '%m/%Y') AS mes_ano, SUM(valor) AS total_valor
                                FROM `logman3__boletos`
                                WHERE CURDATE() >= vencimento 
                                AND (pagamento IS NULL OR pagamento = '0000-00-00')
                                GROUP BY DATE_FORMAT(vencimento, '%m/%Y')
                                ORDER BY DATE_FORMAT(vencimento, '%Y%m');");
// $qtd_ter = $consul_terceiros->fetch_array(MYSQLI_BOTH);
while($qtd_que = $consul_quebra->fetch_array()){
    $dados[]=array(
    "mesano"    =>$qtd_que[0]
    ,"total"    =>$qtd_que[1]
    
    );
}

echo json_encode($dados);