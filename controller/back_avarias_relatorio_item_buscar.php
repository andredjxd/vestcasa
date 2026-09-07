<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$idItem = (int) ($_POST['id_item'] ?? 0);

if ($idItem <= 0) {
    echo json_encode([
        "error" => 100,
        "message" => "ID do item não informado."
    ]);
    exit;
}

try {

    // ======================================================
    // BUSCA O ITEM
    // ======================================================
    $stmt = $cnx->prepare("
        SELECT 
            id,
            codigo_avaria,
            codigo_barras,
            tipo_avaria,
            quantidade,
            observacao
        FROM vest_avarias_relatorio_itens
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $idItem);
    $stmt->execute();

    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if (!$item) {
        echo json_encode([
            "error" => 100,
            "message" => "Item não encontrado."
        ]);
        exit;
    }

    // ======================================================
    // BUSCA FOTOS DO ITEM
    // Usa thumb_arquivo para preview rápido
    // ======================================================
    $stmtFotos = $cnx->prepare("
        SELECT 
            id,
            item_id,
            nome_arquivo,
            caminho_arquivo,
            thumb_arquivo,
            url_arquivo,
            tipo_arquivo,
            tamanho_arquivo
        FROM vest_avarias_relatorio_fotos
        WHERE item_id = ?
        ORDER BY id ASC
    ");

    $stmtFotos->bind_param("i", $idItem);
    $stmtFotos->execute();

    $resultFotos = $stmtFotos->get_result();

    $fotos = [];

    while ($foto = $resultFotos->fetch_assoc()) {

        /*
          Compatibilidade:
          - preview_url usa thumb_arquivo se existir
          - foto_url usa url_arquivo ou caminho_arquivo
        */
        $foto['preview_url'] = !empty($foto['thumb_arquivo'])
            ? $foto['thumb_arquivo']
            : (!empty($foto['url_arquivo']) ? $foto['url_arquivo'] : $foto['caminho_arquivo']);

        $foto['foto_url'] = !empty($foto['url_arquivo'])
            ? $foto['url_arquivo']
            : $foto['caminho_arquivo'];

        $fotos[] = $foto;
    }

    echo json_encode([
        "error" => 101,
        "message" => "Item localizado.",
        "item" => $item,
        "fotos" => $fotos
    ]);

} catch (Throwable $e) {

    error_log("Erro ao buscar item avaria: " . $e->getMessage());

    echo json_encode([
        "error" => 102,
        "message" => "Erro ao buscar item da avaria.",
        "debug" => $e->getMessage()
    ]);
}