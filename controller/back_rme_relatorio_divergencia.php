<?php
include '../assets/_db/db.php';
header('Content-Type: application/json');

function formatarDivergencia($texto) {
    if (!$texto) return '';

    // Remove quebras e normaliza espaços
    $texto = preg_replace('/\s+/', ' ', $texto);

    // Extrai NOTA e MOTIVO
    preg_match('/NOTA:\s*(\d+)/', $texto, $notaMatch);
    preg_match('/MOTIVO:\s*(.*)/', $texto, $motivoMatch);

    $nota   = $notaMatch[1] ?? '';
    $motivo = $motivoMatch[1] ?? '';

    // Divide por SKU
    $partes = preg_split('/(?=SKU:)/', $texto);

    $resultado = [];

    foreach ($partes as $parte) {
        if (trim($parte) == '' || strpos($parte, 'SKU:') === false) continue;

        preg_match('/SKU:\s*(\d+)/', $parte, $sku);
        preg_match('/EAN:\s*(\d+)/', $parte, $ean);
        preg_match('/DESCRIÇÃO:\s*(.*?)\s+QUANTIDADE:/', $parte, $desc);
        preg_match('/QUANTIDADE:\s*(\d+\s+UNIDADES)/', $parte, $qtd);

        $resultado[] =
            "SKU: " . ($sku[1] ?? '') . "\n" .
            "EAN: " . ($ean[1] ?? '') . "\n" .
            "DESCRIÇÃO: " . trim($desc[1] ?? '') . "\n" .
            "QUANTIDADE: " . ($qtd[1] ?? '') . "\n" .
            "NOTA:" . $nota . "\n" .
            "MOTIVO: " . $motivo;
    }

    return implode("\n\n", $resultado);
}

$chamado = $_POST['chamado'] ?? null;
// $chamado = 253503;

$dados = [];

if (!$chamado) {
    echo json_encode($dados);
    exit;
}

$query = "
    SELECT vrrd.id, vrrd.nota, vrrd.divergencia FROM vest_rme_recebimento_divergencias AS vrrd
    LEFT JOIN vest_rme_recebimento AS vrr ON vrr.id = vrrd.recebimento_id
    WHERE vrr.chamado = ?;
";

$stmt = $cnx->prepare($query);
$stmt->bind_param("s", $chamado);
$stmt->execute();
$result = $stmt->get_result();

while ($dad = $result->fetch_assoc()) {
    $dados[] = [
         "iddiv"           => $dad['id']
        ,"nota"             => $dad['nota']
        ,"divergencia"      => formatarDivergencia($dad['divergencia'])

    ];
}

$stmt->close();
$cnx->close();

echo json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
