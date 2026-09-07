<?php
// Configura SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

set_time_limit(0);
ignore_user_abort(true);

// Evita erro de buffer inexistente
if (ob_get_level() > 0) {
    ob_end_flush();
}

ob_implicit_flush(true);

// 🔥 Força início do stream (ESSENCIAL)
echo ":" . str_repeat(" ", 2048) . "\n\n";
@ob_flush();
@flush();

// Captura erro fatal
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        echo "data: ❌ Erro fatal: {$error['message']}\n\n";
        @ob_flush();
        @flush();
    }
});


$basePath = dirname(__DIR__, 1);
require_once $basePath . '/assets/init.php';
require_once $basePath . '/assets/_db/functions/exportData.php';

// Conexão
try {
    $db = new PDO(
        "mysql:host=" . BD_SERVIDOR . ";dbname=" . BD_BANCO . ";charset=utf8mb4",
        BD_USUARIO,
        BD_SENHA
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    sseMessage("❌ Erro ao conectar: " . $e->getMessage());
    exit;
}

// Execução
$outputDir = realpath(dirname(__DIR__) . '/assets/_db') . '/dados/';
exportData($db, $outputDir);

exit;