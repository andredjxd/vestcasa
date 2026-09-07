<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../assets/_db/db.php';

$response = [];
ob_start();

try {
    // Cria tabela de controle de migrations
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL UNIQUE,
            executado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    $migrationsPath = __DIR__ . '/../assets/_db/migrations';
    $files = glob($migrationsPath . '/*.php');
    sort($files);

    foreach ($files as $file) {
        $filename = basename($file);
        echo "🚀 Verificando migration: $filename\n";

        $migration = include $file;
        $migration($pdo);

        // Registra execução
        $stmt = $pdo->prepare("
            INSERT INTO migrations (nome, executado_em)
            VALUES (?, NOW())
            ON DUPLICATE KEY UPDATE executado_em = NOW()
        ");
        $stmt->execute([$filename]);
    }

    echo "✅ Todas as migrations foram sincronizadas com sucesso!\n";

    $response['status'] = 'success';
    $response['log'] = ob_get_clean();

} catch (Throwable $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
    $response['log'] = ob_get_clean();
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
