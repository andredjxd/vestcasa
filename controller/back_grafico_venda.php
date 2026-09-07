<?php
include '../assets/_db/db.php';

$sql = $cnx->query("SELECT 
            EXTRACT(YEAR FROM data) AS ano, 
            EXTRACT(MONTH FROM data) AS mes, 
            SUM(vendaDia) AS total 
        FROM logman3__svdbi62 
        GROUP BY ano, mes 
        ORDER BY ano, mes;");

// Inicializa o array para evitar erro se não houver dados
$dados = [];

while ($dad = $sql->fetch_assoc()) { 
    $dados[] = [
        "ano" => $dad['ano'],
        "mes" => $dad['mes'],
        "total" => $dad['total']
    ];
}

// Verifica se há dados para evitar JSON inválido
echo json_encode($dados ?: []);
?>
