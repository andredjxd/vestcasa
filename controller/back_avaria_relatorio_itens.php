<?php
include '../assets/_db/db.php';
header('Content-Type: application/json');

$avaria = $_POST['avaria'] ?? null;
// $avaria = 29857;

$dados = [];

if (!$avaria) {
    echo json_encode($dados);
    exit;
}

$query = "
        SELECT 
            id,
            codigo_avaria,
            codigo_barras,
            produto,
            descricao_avaria,
            tipo_avaria,
            quantidade,
            preco_contrapartida,
            observacao
        FROM vest_avarias_relatorio_itens 
        WHERE codigo_avaria = ?;
";

$stmt = $cnx->prepare($query);
$stmt->bind_param("s", $avaria);
$stmt->execute();
$result = $stmt->get_result();

$pos = 0;
while ($dad = $result->fetch_assoc()) {
    $pos++;
    $dados[] = [
         "idava"            => $dad['id']
        ,"pos"              => $pos
        ,"codavaria"        => $dad['codigo_avaria']
        ,"codbarras"        => $dad['codigo_barras']
        ,"descricao"        => $dad['produto']
        ,"descricaoavaria"  => $dad['descricao_avaria']
        ,"tipoavaria"       => $dad['tipo_avaria']
        ,"quantidade"       => $dad['quantidade']
        ,"valor"            => $dad['preco_contrapartida']
        ,"observacao"       => $dad['observacao']
        
    ];
}

$stmt->close();
$cnx->close();

echo json_encode($dados, JSON_UNESCAPED_UNICODE);
