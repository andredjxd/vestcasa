<?php
include '../assets/_db/db.php';

/* =========================================
   CONFIGURAÇÃO SSE
========================================= */

set_time_limit(0);
ignore_user_abort(true);

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

/* Desativa buffering do servidor */
if (ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(true);


/* =========================================
   TIPO DA CONSULTA
========================================= */

$tipo = $_GET['tipo'] ?? 'fila';


/* =========================================
   LOOP SSE
========================================= */

while (true) {

    /* Se cliente desconectou, encerra */
    if (connection_aborted()) {
        break;
    }

    if ($tipo === 'agendadas') {

        $sql = $cnx->query("
            SELECT 
                id,
                nome_processo,
                parametros,
                total,
                processados,
                progresso,
                status,
                log,
                data_agendada,
                SEC_TO_TIME(
                    TIMESTAMPDIFF(
                        SECOND,
                        data_inicio,
                        IFNULL(data_fim, NOW())
                    )
                ) AS tempo_execucao,
                created
            FROM vest_relatorio_fila_execucao
            WHERE agendado = 1
            ORDER BY 
                CASE status
                    WHEN 1 THEN 1   -- Executando (primeiro)
                    WHEN 0 THEN 2   -- Fila
                    WHEN 9 THEN 3   -- Agendada
                    WHEN status IN (2,3) THEN 3 -- Concluido e Erro
                    ELSE 5
                END,
                -- Se for Fila (0), ordena crescente
                CASE WHEN status = 0 THEN id END ASC,
                
                -- Para os demais, mantém decrescente
                CASE WHEN status <> 0 THEN id END DESC
            LIMIT 100
        ");

    } else {

        $sql = $cnx->query("
            SELECT 
                id,
                nome_processo,
                parametros,
                total,
                processados,
                progresso,
                status,
                log,
                SEC_TO_TIME(
                    TIMESTAMPDIFF(
                        SECOND,
                        data_inicio,
                        IFNULL(data_fim, NOW())
                    )
                ) AS tempo_execucao,
                created
            FROM vest_relatorio_fila_execucao
            WHERE agendado = 0
            ORDER BY 
                CASE status
                    WHEN 1 THEN 1  -- Executando
                    WHEN 0 THEN 2  -- Fila
                    WHEN status IN (2,3) THEN 3 -- Concluido e Erro
                    ELSE 4
                END,
                
                -- Se for Fila (0), ordena crescente
                CASE WHEN status = 0 THEN id END ASC,
                
                -- Para os demais, mantém decrescente
                CASE WHEN status <> 0 THEN id END DESC
            LIMIT 100;
        ");
    }

    $dados = [];

    while ($row = $sql->fetch_assoc()) {
        $dados[] = $row;
    }

    echo "data: " . json_encode($dados) . "\n\n";

    @ob_flush();
    @flush();

    sleep(2);
}