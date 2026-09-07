<?php
include '../assets/_db/db.php';

$sql = $cnx->query("SELECT DATE_FORMAT(data, '%m/%Y') AS mes_ano, 
                           anterior, previsto, realizado 
                    FROM `logman3__budget_vendas` 
                    WHERE tipo = 'ror'");

// Inicializa o array para evitar erro se não houver dados
$dados = [];

while ($dad = $sql->fetch_assoc()) {
    // Montar o array final
    $dados[] = [
        "data" => $dad['mes_ano'],
        "anterior" => $dad['anterior'],
        "previsto" => $dad['previsto'],
        "realizado" => ($dad['realizado'] == 0.00) ? null : $dad['realizado']
    ];
}

// Retornar JSON
echo json_encode($dados);
?>
