<?php
// Arquivo gerado automaticamente
require_once __DIR__ . '/../functions/syncTable.php';

return function($db) {
    syncTable($db, 'vest_relatorio_fila_execucao', [

        'id'               => "int(11) unsigned NOT NULL AUTO_INCREMENT",

        // Identificação do processo
        'nome_processo'    => "varchar(150) NOT NULL",
        'parametros'       => "text NULL",

        // Controle de progresso (SSE / barra)
        'total'            => "int(11) NOT NULL DEFAULT 0",
        'processados'      => "int(11) NOT NULL DEFAULT 0",
        'progresso'        => "decimal(5,2) NOT NULL DEFAULT 0.00",

        // Status da fila
        // 0 = fila
        // 1 = executando
        // 2 = concluido
        // 3 = erro
        'status'           => "tinyint(1) NOT NULL DEFAULT 0",

        // Logs e controle
        'log'              => "longtext NULL",

        // Datas (seguindo seu padrão)
        'data_inicio'      => "datetime NULL",
        'data_fim'         => "datetime NULL",
        'created'          => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",

        // Intervalo para recorrência horária
        'hora_inicio' => "time NULL",
        'hora_fim'    => "time NULL",

        // ==============================
        // AGENDAMENTO
        // ==============================

        // 0 = execução imediata
        // 1 = agendado
        'agendado'              => "tinyint(1) NOT NULL DEFAULT 0",

        // Data e hora que deve entrar na fila
        'data_agendada'         => "datetime NULL",

        // 0 = ainda não liberado
        // 1 = já liberado para fila
        'agendamento_liberado'  => "tinyint(1) NOT NULL DEFAULT 0",

        // Índice para performance
        'INDEX:idx_agendamento' => "(agendado, agendamento_liberado, data_agendada)",
        
        // ==============================
        // RECORRÊNCIA
        // ==============================

        // 0 = não recorrente
        // 1 = recorrente
        'recorrente' => "tinyint(1) NOT NULL DEFAULT 0",

        // D = diário (futuramente pode ter S = semanal)
        'tipo_recorrencia' => "char(1) NULL",

        // Guarda horário fixo do disparo (ex: 23:00:00)
        'hora_recorrencia' => "time NULL",

        'INDEX:idx_recorrente' => "(recorrente, tipo_recorrencia)",

        // Índices importantes para o worker Python
        'INDEX:idx_status' => "(status)",
        'INDEX:idx_created'=> "(created)",
        'INDEX:idx_intervalo' => "(hora_inicio, hora_fim)",


    ]);
};


?>
