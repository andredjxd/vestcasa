<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$dados = json_decode(file_get_contents("php://input"), true);

$itens = $dados['itens'] ?? [];
$chamado = $dados['chamado'] ?? null;

/* 🔒 VALIDAÇÃO */
if (empty($itens)) {
    echo json_encode([
        "error" => "102",
        "message" => "Nenhum item recebido"
    ]);
    exit;
}

try {

    $cnx->begin_transaction();

    // 🔥 Atualiza itens
    $stmt = $cnx->prepare("
        UPDATE vest_rme_recebimento_itens
        SET qtde_conferida = ?
        WHERE id = ?
    ");

    foreach ($itens as $item) {

        $id  = $item['iditem'] ?? null;
        $qtd = $item['qtd_conferida'] ?? 0;

        if (!$id) continue;

        $stmt->bind_param("ii", $qtd, $id);
        $stmt->execute();
    }

    // 🔥 Atualiza status UMA VEZ só
    if ($chamado) {

        $stmt = $cnx->prepare("
            UPDATE vest_rme_recebimento
            SET status = 2
            WHERE chamado = ?
        ");

        $stmt->bind_param("i", $chamado);
        $stmt->execute();
    }

    $cnx->commit();

    echo json_encode([
        "error" => "101",
        "message" => "Processo finalizado com sucesso"
    ]);

} catch (mysqli_sql_exception $e) {

    $cnx->rollback();

    error_log($e->getMessage());

    echo json_encode([
        "error" => "100",
        "message" => "Erro ao salvar no banco"
    ]);
}


?>