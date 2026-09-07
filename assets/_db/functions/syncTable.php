<?php

function sseMessage($msg)
{
    echo "data: $msg\n\n";
    @ob_flush();
    @flush();
}

function syncTable(PDO $db, string $table, array $columns)
{
    // 1️⃣ Cria a tabela se não existir
    $stmt = $db->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() === 0) {
        $db->exec("
            CREATE TABLE `$table` (
                `id` INT AUTO_INCREMENT PRIMARY KEY
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        sseMessage("📦 Tabela `$table` Criada.");
        usleep(100000);
    }

    // 2️⃣ Obtém colunas atuais
    $stmt = $db->query("SHOW COLUMNS FROM `$table`");
    $existing = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        $existing[$col['Field']] = $col['Type'];
    }

    // 🔹 Obtém índices existentes
    $stmt = $db->query("SHOW INDEX FROM `$table`");
    $existingIndexes = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $idx) {
        if ($idx['Key_name'] !== 'PRIMARY') {
            $existingIndexes[$idx['Key_name']] = true;
        }
    }

    // 3️⃣ Adiciona ou atualiza colunas / índices
    foreach ($columns as $name => $definition) {

        // 🔹 Se for índice UNIQUE
        if (str_starts_with($name, 'UNIQUE:')) {

            $indexName = str_replace('UNIQUE:', '', $name);

            if (!isset($existingIndexes[$indexName])) {
                $db->exec("ALTER TABLE `$table` ADD UNIQUE `$indexName` $definition;");
                sseMessage("🟣 Índice UNIQUE `$indexName` criado.");
                usleep(100000);
            }

            continue;
        }

        // 🔹 Se for índice NORMAL
        if (str_starts_with($name, 'INDEX:')) {

            $indexName = str_replace('INDEX:', '', $name);

            if (!isset($existingIndexes[$indexName])) {
                $db->exec("ALTER TABLE `$table` ADD INDEX `$indexName` $definition;");
                sseMessage("🔵 Índice `$indexName` criado.");
                usleep(100000);
            }

            continue;
        }

        // 🔹 Coluna normal
        if (!array_key_exists($name, $existing)) {

            $db->exec("ALTER TABLE `$table` ADD COLUMN `$name` $definition;");
            sseMessage("🔵 Coluna `$name` adicionada.");
            usleep(100000);

        } else {

            // Verifica se o tipo mudou (simplificado)
            $stmt = $db->prepare("SHOW FIELDS FROM `$table` LIKE ?");
            $stmt->execute([$name]);
            $field = $stmt->fetch(PDO::FETCH_ASSOC);

            $currentType = strtolower($field['Type']);
            $expectedType = strtolower(preg_replace('/\s+/', ' ', trim(explode(' ', $definition)[0])));

            if (strpos($currentType, $expectedType) === false) {
                $db->exec("ALTER TABLE `$table` MODIFY COLUMN `$name` $definition;");
                sseMessage("🟡 Coluna `$name` atualizada (tipo alterado).");
                usleep(100000);
            }
        }
    }

    // 4️⃣ Remove colunas que não estão mais no schema (exceto id)
    foreach ($existing as $name => $type) {
        if ($name === 'id') continue;

        if (!array_key_exists($name, $columns)) {
            $db->exec("ALTER TABLE `$table` DROP COLUMN `$name`;");
            sseMessage("🔴 Coluna `$name` removida.");
            usleep(100000);
        }
    }

    usleep(100000);
    sseMessage("✅ Tabela `$table` sincronizada!");
}
