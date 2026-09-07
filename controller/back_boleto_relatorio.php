<?php
include '../assets/_db/db.php';
$dados = array();
$contador = 1; // Inicializa o contador

$consul_quebra = $cnx->query("SELECT filial, razao, cnpj_cpf, vencimento, pagamento, valor, titulo, datebase
                                FROM logman3__boletos  
                                WHERE ((pagamento IS NOT NULL AND (CURDATE() - INTERVAL 5 DAY) <= vencimento) 
                                OR (pagamento IS NULL))");

while($qtd_que = $consul_quebra->fetch_array()){
    // Adiciona o contador e os dados no array
    $dados[] = array(
        "contador"      => $contador
        ,"filial"       => $qtd_que[0]
        ,"razao"        => $qtd_que[1]
        ,"cnpj"         => $qtd_que[2]
        ,"vencimento"   => $qtd_que[3]
        ,"pagamento"    => $qtd_que[4]
        ,"valor"        => $qtd_que[5]
        ,"titulo"       => $qtd_que[6]
        ,"datebase"     => $qtd_que[7]
    );
    
    // Incrementa o contador
    $contador++;
}

echo json_encode($dados);
?>
