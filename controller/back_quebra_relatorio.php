<?php
include '../assets/_db/db.php';
$dados = array();
$contador = 1; // Inicializa o contador

$consul_quebra = $cnx->query("SELECT op.login, qc.operador, op.nome, qc.dt_quebra, qc.valor_qb, qc.valor_pg, qc.resta_pg  AS total_resta_pg
                                FROM logman3__quebras_caixa AS qc LEFT JOIN logman3__operadores AS op ON qc.operador = op.operador  
                                ORDER BY `op`.`nome` ASC,qc.dt_quebra;");

while($qtd_que = $consul_quebra->fetch_array()){
    // Adiciona o contador e os dados no array
    $dado[] = array(
        "contador"     => $contador,
        "login"        => $qtd_que[0],
        "operador"     => $qtd_que[1],
        "nome"         => $qtd_que[2],
        "dtquebra"     => date('d/m/Y',strtotime($qtd_que[3])),
        "quebra"       => $qtd_que[4],
        "quebrapg"     => $qtd_que[5],
        "totalresta"   => $qtd_que[6]
    );
    
    // Incrementa o contador
    $contador++;
}

echo json_encode($dado);
?>
