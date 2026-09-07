<?php
include '../assets/_db/db.php';
header('Content-Type: application/json');

$chamado = $_POST['chamado'] ?? null;
// $chamado = 218799;

$dados = [];

if (!$chamado) {
    echo json_encode($dados);
    exit;
}

$query = "
        SELECT 
            vrri.id,
            vrr.cnpj,
            vrr.pedido,
            vrr.nota,
            vrr.data_faturamento,
            vrri.sku,
            vpc.produto,
            vpc.marca,
            vrri.qtde_esperada,
            vrri.qtde_conferida
        FROM vest_rme_recebimento_itens AS vrri
        LEFT JOIN vest_produto_cadastro AS vpc
            ON vrri.sku = vpc.sku
        LEFT JOIN vest_rme_recebimento AS vrr
            ON vrri.recebimento_id = vrr.id
        WHERE vrr.chamado = ?;
";

$stmt = $cnx->prepare($query);
$stmt->bind_param("s", $chamado);
$stmt->execute();
$result = $stmt->get_result();

$pos = 0;
while ($dad = $result->fetch_assoc()) {
    $pos++;
    $dados[] = [
         "iditem"           => $dad['id']
        ,"pos"              => $pos
        ,"cpnj"             => $dad['cnpj']
        ,"pedido"           => $dad['pedido']
        ,"nota"             => $dad['nota']
        ,"datafaturamento"  => $dad['data_faturamento']
        ,"sku"              => $dad['sku']
        ,"produto"          => $dad['produto']
        ,"marca"            => $dad['marca']
        ,"qtdesperada"      => $dad['qtde_esperada']
        ,"qtdconferida"     => $dad['qtde_conferida']
    ];
}

$stmt->close();
$cnx->close();

echo json_encode($dados, JSON_UNESCAPED_UNICODE);
