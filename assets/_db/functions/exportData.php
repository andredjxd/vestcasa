<?php
// 🔧 Função auxiliar para enviar mensagens SSE
function sseMessage($message) {
    echo "data: " . $message . "\n\n";
    @ob_flush();
    @flush();
}

// 🔧 Função principal de exportação
function exportData($db, $outputDir) {
    date_default_timezone_set('America/Sao_Paulo');
    sseMessage("🗜️ Iniciado processo de backup do banco de dados!");

    sleep(3);

    // Cria pasta se não existir
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
        sseMessage("📁 Pasta output criada: $outputDir");
    }

    $timestamp = date('Ymd_His');
    $stmt = $db->query("SHOW TABLES");

    $exportedFiles = [];

    while ($table = $stmt->fetch(PDO::FETCH_NUM)) {
        $tableName = $table[0];
        sseMessage("📦 Exportando tabela: {$tableName}");

        $fileName = rtrim($outputDir, '/') . "/{$timestamp}_{$tableName}.json";

        // 🔥 ALTERAÇÃO AQUI (remove fetchAll)
        $handle = fopen($fileName, 'w');

        if ($handle === false) {
            sseMessage("❌ Erro ao criar arquivo: $fileName");
            sseMessage("📂 Existe diretório? " . (is_dir(dirname($fileName)) ? 'SIM' : 'NÃO'));
            sseMessage("✏️ Gravável? " . (is_writable(dirname($fileName)) ? 'SIM' : 'NÃO'));
            continue;
        }

        fwrite($handle, "[");

        $stmtData = $db->query("SELECT * FROM `$tableName`");

        $first = true;
        $count = 0;

        while ($row = $stmtData->fetch(PDO::FETCH_ASSOC)) {

            if (!$first) {
                fwrite($handle, ",");
            }

            fwrite($handle, json_encode($row, JSON_UNESCAPED_UNICODE));
            $first = false;
            $count++;

            // 🔥 mantém SSE vivo (evita queda)
            // if ($count % 500 == 0) {
            //     sseMessage("🔄 {$tableName}... {$count} registros");
            // }
        }

        fwrite($handle, "]");
        fclose($handle);

        $exportedFiles[] = $fileName;
        usleep(200000);
    }

    // 🔧 pequena correção (variável antes de usar)
    $zipFile = rtrim($outputDir, '/') . "/{$timestamp}_export.zip";
    sseMessage("🗜️ Inciciando processo de compactar os arquivos, Aguarde...");

    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        foreach ($exportedFiles as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        usleep(200000);
        sseMessage("🗜️ Arquivos compactados em ZIP!");

        foreach ($exportedFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $allZips = glob($outputDir . '*_export.zip');
        if (count($allZips) > 2) {
            usort($allZips, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            for ($i = 2; $i < count($allZips); $i++) {
                if (file_exists($allZips[$i])) {
                    unlink($allZips[$i]);
                    usleep(500000);
                    sseMessage("🗑️ ZIP antigo removido!");
                }
            }
        }

    } else {
        sseMessage("❌ Falha ao criar ZIP.");
    }

    usleep(500000);
    sseMessage("🏁 Exportação finalizada!");
    sseMessage("[FIM]");
}