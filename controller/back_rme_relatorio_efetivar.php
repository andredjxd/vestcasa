<?php
require '../assets/_db/db.php';

// $chamado = 234616;
$chamado = $_POST['chamado'] ?? null;
$usuario = gethostname();

try {

    $cnx->begin_transaction();

    // 🔥 Buscar itens de TODOS recebimentos do chamado
    $sql = "
        SELECT 
            r.id as id_recebimento,
            r.data_recebimento as datarecebimento,
            i.*
        FROM vest_rme_recebimento r
        JOIN vest_rme_recebimento_itens i 
            ON i.recebimento_id = r.id
        WHERE r.chamado = ?
        AND r.status IN (1,2)
    ";
    $stmt = $cnx->prepare($sql);
    $stmt->bind_param("i", $chamado);
    $stmt->execute();
    $res = $stmt->get_result();

    $recebimentos = [];

    while ($item = $res->fetch_assoc()) {

        $id_recebimento = $item['id_recebimento'];
        $sku = $item['sku'];
        $datarecebimento = $item['datarecebimento'];
        $qtd = !empty($item['qtde_conferida']) 
                    ? $item['qtde_conferida'] 
                    : $item['qtde_esperada'];

        // Guarda os recebimentos para finalizar depois
        $recebimentos[$id_recebimento] = true;

        // 🔹 ESTOQUE
        $sql = "
            INSERT INTO vest_produto_estoque (sku, quantidade)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE quantidade = quantidade + VALUES(quantidade)
        ";
        $stmt2 = $cnx->prepare($sql);
        $stmt2->bind_param("si", $item['sku'], $qtd);
        $stmt2->execute();

        // 🔹 EXTRATO
        $sql = "
            INSERT INTO vest_produto_extrato 
            (sku, tipo_movimento, documento, qtde, data_movimento, usuario, data_recebimento)
            VALUES (?, 'ENTRADA', ?, ?, NOW(), ?, ?)
        ";
        $stmt3 = $cnx->prepare($sql);
        $stmt3->bind_param("sisss", $item['sku'], $id_recebimento, $qtd, $usuario, $datarecebimento);
        $stmt3->execute();
        // 🔹 LOG
        $sql = "
            INSERT INTO vest_produto_movimentacao_log
            (sku, tipo, usuario, documento, descricao)
            VALUES (?, 'ENTRADA', ?, ?, 'Recebimento de carga')
        ";
        $stmt4 = $cnx->prepare($sql);
        $stmt4->bind_param("sss", $item['sku'], $usuario, $id_recebimento);
        $stmt4->execute();
    }

    // 🔥 FINALIZAR TODOS OS RECEBIMENTOS DO CHAMADO
    foreach ($recebimentos as $id_recebimento => $v) {

        $sql = "
            UPDATE vest_rme_recebimento 
            SET status = 3, data_fechamento = NOW() 
            WHERE id = ? 
        ";
        $stmt6 = $cnx->prepare($sql);
        $stmt6->bind_param("i", $id_recebimento);
        $stmt6->execute();
    }

    $cnx->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Carga do chamado efetivada com sucesso!"
    ]);

} catch (Exception $e) {

    $cnx->rollback();

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}