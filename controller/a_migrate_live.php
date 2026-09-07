<?php
/**
 * Script de execução de migrations via SSE (Server-Sent Events)
 * Autor: Andre Alves
 * Data: 2025-10-23
 */

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // desativa buffering no Nginx

// Garante que os buffers PHP não bloqueiem a saída
if (ob_get_level() == 0) {
    ob_implicit_flush(true);
} else {
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
}

/**
 * Envia mensagens SSE ao cliente
 */
function sendEvent($msg) {
    echo "data: " . trim($msg) . "\n\n";
    @ob_flush();
    @flush();
    usleep(100000); // pequena pausa para estabilidade no envio
}

// Caminhos base
$basePath = dirname(__DIR__, 1);
require_once $basePath . '/assets/init.php';
// require_once $basePath . '/assets/_db/functions/exportData.php';

// Conexão com o banco de dados MySQL via PDO
try {
    $pdo = new PDO(
        "mysql:host=" . BD_SERVIDOR . ";dbname=" . BD_BANCO . ";charset=utf8mb4",
        BD_USUARIO,
        BD_SENHA
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    sendEvent("❌ Erro na conexão com o banco: " . $e->getMessage());
    sendEvent("[FIM]");
    exit;
}

// Cria tabela de controle das migrations
try {
    sendEvent("🔍 Verificando estrutura da tabela migrations...");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL UNIQUE,
            executado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    sendEvent("✅ Estrutura da tabela migrations verificada.");

    $migrationsPath = __DIR__ . '/../assets/_db/migrations';
    if (!is_dir($migrationsPath)) {
        throw new Exception("Diretório de migrations não encontrado: $migrationsPath");
    }

    $files = glob($migrationsPath . '/*.php');
    sort($files);

    if (empty($files)) {
        sendEvent("⚠️ Nenhuma migration encontrada em $migrationsPath");
    }

    foreach ($files as $file) {
        $filename = basename($file);
        // sendEvent("🚀 Executando migration: $filename");

        try {
            // Inclui migration dentro de buffer isolado para evitar saída indevida
            $migration = include $file;

            if (!is_callable($migration)) {
                throw new Exception("O arquivo $filename não retornou uma função válida.");
            }

            // Executa migration com captura de erros
            try {
                $migration($pdo);
            } catch (Throwable $e) {
                throw new Exception("Erro ao executar função da migration: " . $e->getMessage(), 0, $e);
            }

            // Atualiza registro de execução
            $stmt = $pdo->prepare("
                INSERT INTO migrations (nome, executado_em)
                VALUES (?, NOW())
                ON DUPLICATE KEY UPDATE executado_em = NOW()
            ");
            $stmt->execute([$filename]);

            // sendEvent("✅ Migration concluída: $filename");
        } catch (Throwable $e) {
            sendEvent("❌ Erro em $filename: " . $e->getMessage() . 
                " (Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . ")");
        }
    }


    sendEvent("🎉 Todas as migrations foram sincronizadas com sucesso!");
    sendEvent("[FIM]");
} catch (Throwable $e) {
    sendEvent("❌ Erro geral: " . $e->getMessage());
    sendEvent("[FIM]");
}
