<?php
include '../assets/_db/db.php';

$ini = $_POST['data'] ?? date('Y-m-d');
// $ini = '2026-03-31';
// $pdv = $_POST['pdv'];
// $pdv = 4;

$dados = [];    

// 🔹 Função para traduzir tPag
function tipoPagamento($tpag){
    switch($tpag){
        case '01': return 'DINHEIRO';
        case '02': return 'OUTROS';
        case '03': return 'CREDITO';
        case '04': return 'DEBITO';
        case '90': return 'DEVOLUCAO';
        case '99': return 'VOUCHER';
        default: return 'DESCONHECIDO';
    }
}

// 🔹 Query simples (sem GROUP BY)
$sql = $cnx->query("
SELECT 
    vrv.identificador,
    vrv.pdv,
    vrv.data_hora,
    vrv.num_nf,
    vrv.serie_nf,
    vrv.total,
    vrvp.tPag,
    vrvp.vPag
FROM vest_relatorio_vendas AS vrv
LEFT JOIN vest_relatorio_vendas_pagamentos AS vrvp 
    ON vrvp.identificadornf = vrv.identificador
WHERE vrv.data_hora >= '{$ini} 00:00:00'
  AND vrv.data_hora <  DATE_ADD('{$ini}', INTERVAL 1 DAY)
ORDER BY vrv.num_nf ASC
");
//  AND vrv.pdv = {$pdv}

// 🔹 Agrupar manualmente por identificador (NF)
$vendas = [];

while($row = $sql->fetch_assoc()){

    $id = $row['identificador'];

    // Se ainda não existe a venda, cria
    if(!isset($vendas[$id])){
        $vendas[$id] = [
            "identificador" => $row['identificador'],
            "pdv"           => $row['pdv'],
            "data"          => date('d/m/Y H:i:s', strtotime($row['data_hora'])),
            "nf"            => $row['num_nf'],
            "serie"         => $row['serie_nf'],
            "total"         => $row['total'],
            "pagamentos"    => []
        ];
    }

    // Adiciona pagamento (se existir)
    if(!empty($row['tPag'])){
        $vendas[$id]['pagamentos'][] = 
            tipoPagamento($row['tPag']) . ': ' . number_format($row['vPag'], 2, ',', '.');
    }
}

// 🔹 Monta saída final
foreach($vendas as $v){
    $dados[] = [
        "identificador" => $v['identificador'],
        "pdv"           => $v['pdv'],
        "data"          => $v['data'],
        "nf"            => $v['nf'],
        "serie"         => $v['serie'],
        "total"         => number_format($v['total'], 2, ',', '.'),
        "pagamentos"    => implode(' | ', $v['pagamentos'])
    ];
}

header('Content-Type: application/json');
echo json_encode($dados);