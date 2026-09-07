<?php
include '../assets/_db/db.php';

$dados = [];
$sql = $cnx->query("SELECT 
                        DATE_FORMAT(m.data, '%m/%Y') AS mes_ano,
                        COALESCE(SUM(v.total), 0) AS total_vendido,
                        m.venda_meta
                    FROM vest_relatorio_vendas_meta m
                    LEFT JOIN vest_relatorio_vendas v
                        ON YEAR(v.data_hora) = YEAR(m.data)
                        AND MONTH(v.data_hora) = MONTH(m.data)
                    GROUP BY YEAR(m.data), MONTH(m.data), m.venda_meta
                    ORDER BY YEAR(m.data), MONTH(m.data);");

while ($dad = $sql->fetch_assoc()) {

    // Montar o array final
    $dados[] = [
        "data" => $dad['mes_ano'],
        "metames" => $dad['venda_meta'],
        "totalvendido" => $dad['total_vendido']
    ];
}

header('Content-Type: application/json');
echo json_encode($dados);
?>
