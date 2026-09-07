<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

$usuario = gethostname();

try {
    $cnx->begin_transaction();

    $query = "
        SELECT
            id,
            sku,
            produto,
            quantidade_ajuste
        FROM vest_mercadoria_ajuste
        WHERE status = 1
        ORDER BY id ASC
        FOR UPDATE
    ";

    $res = $cnx->query($query);

    if (!$res || $res->num_rows === 0) {
        throw new Exception("Nenhum item pendente para ajuste");
    }

    $totalItens = 0;

    while ($item = $res->fetch_assoc()) {
        $id = (int)$item['id'];
        $sku = $item['sku'];
        $qtdAjuste = (int)$item['quantidade_ajuste'];

        $stmtEstoqueAtual = $cnx->prepare("
            SELECT quantidade
            FROM vest_produto_estoque
            WHERE sku = ?
            FOR UPDATE
        ");
        $stmtEstoqueAtual->bind_param("s", $sku);
        $stmtEstoqueAtual->execute();
        $resEstoqueAtual = $stmtEstoqueAtual->get_result();

        $estoqueAnterior = 0;

        if ($resEstoqueAtual && $resEstoqueAtual->num_rows > 0) {
            $rowEstoque = $resEstoqueAtual->fetch_assoc();
            $estoqueAnterior = (int)$rowEstoque['quantidade'];
        }

        $estoquePosterior = $estoqueAnterior + $qtdAjuste;

        $stmtEstoque = $cnx->prepare("
            INSERT INTO vest_produto_estoque (sku, quantidade, updated_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                quantidade = VALUES(quantidade),
                updated_at = NOW()
        ");
        $stmtEstoque->bind_param("si", $sku, $estoquePosterior);
        $stmtEstoque->execute();

        $documento = "AJUSTE-" . $id;
        $descricao = "Ajuste manual de mercadoria";

        $stmtExtrato = $cnx->prepare("
            INSERT INTO vest_produto_extrato
                (sku, tipo_movimento, documento, qtde, saldo, usuario, data_movimento)
            VALUES
                (?, 'AJUSTE', ?, ?, ?, ?, NOW())
        ");
        $stmtExtrato->bind_param(
            "ssiis",
            $sku,
            $documento,
            $qtdAjuste,
            $estoquePosterior,
            $usuario
        );
        $stmtExtrato->execute();

        $stmtLog = $cnx->prepare("
            INSERT INTO vest_produto_movimentacao_log
                (sku, tipo, usuario, documento, descricao)
            VALUES
                (?, 'AJUSTE', ?, ?, ?)
        ");
        $stmtLog->bind_param("ssss", $sku, $usuario, $documento, $descricao);
        $stmtLog->execute();

        $stmtAjuste = $cnx->prepare("
            UPDATE vest_mercadoria_ajuste
            SET
                status = 2,
                estoque_anterior = ?,
                estoque_posterior = ?,
                usuario_ajuste = ?,
                data_ajuste = NOW(),
                updated_at = NOW()
            WHERE id = ? AND status = 1
        ");
        $stmtAjuste->bind_param(
            "iisi",
            $estoqueAnterior,
            $estoquePosterior,
            $usuario,
            $id
        );
        $stmtAjuste->execute();

        $totalItens++;
    }

    $cnx->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Ajuste realizado com sucesso",
        "total" => $totalItens
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $cnx->rollback();

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
