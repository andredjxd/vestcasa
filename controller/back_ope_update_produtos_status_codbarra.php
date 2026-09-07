<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$codigo = $_POST['codigo'] ?? null;
$status = isset($_POST['status']) ? (int)$_POST['status'] : null;

if (!$codigo || ($status !== 0 && $status !== 1)) {
    echo json_encode([
        "success" => false,
        "message" => "Parâmetros inválidos"
    ]);
    exit;
}

// 🔍 busca o SKU do código
$stmt = $cnx->prepare("
    SELECT sku FROM vest_produto_codigo_barras 
    WHERE codigo_barras = ?
");
$stmt->bind_param("s", $codigo);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    echo json_encode([
        "success" => false,
        "message" => "Código não encontrado"
    ]);
    exit;
}

$sku = $row['sku'];

$cnx->begin_transaction();

try {

    if ($status == 1) {

        // 🔥 zera todos do mesmo SKU
        $stmt = $cnx->prepare("
            UPDATE vest_produto_codigo_barras 
            SET status = 0 
            WHERE sku = ?
        ");
        $stmt->bind_param("s", $sku);
        $stmt->execute();

        // 🔥 ativa apenas o selecionado
        $stmt = $cnx->prepare("
            UPDATE vest_produto_codigo_barras 
            SET status = 1 
            WHERE codigo_barras = ?
        ");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();

    } else {

        // opcional: permitir desmarcar
        $stmt = $cnx->prepare("
            UPDATE vest_produto_codigo_barras 
            SET status = 0 
            WHERE codigo_barras = ?
        ");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
    }

    $cnx->commit();

    echo json_encode([
        "success" => true
    ]);

} catch (Exception $e) {

    $cnx->rollback();

    echo json_encode([
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
    ]);
}