<?php
include '../assets/_db/db.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

while (true) {

    $sql = $cnx->query("
        SELECT 
            id,
            nome_processo,
            total,
            processados,
            progresso,
            status,
            created
        FROM vest_relatorio_fila_execucao
        WHERE status IN (9)
        ORDER BY id DESC
        LIMIT 12
    ");

    $dados = [];

    while ($row = $sql->fetch_assoc()) {
        $dados[] = $row;
    }

    echo "data: " . json_encode($dados) . "\n\n";

    ob_flush();
    flush();

    sleep(2); // atualiza a cada 2 segundos
}
