<?php

function sseMessage($msg)
{
    echo "data: $msg\n\n";
    @ob_flush();
    @flush();
}

function syncTable(PDO $db, string $table, array $columns)
{
    // sseMessage("🔍 Verificando tabela `$table`...");

    // 1️⃣ Cria a tabela se não existir
    $stmt = $db->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() === 0) {
        $db->exec("
            CREATE TABLE `$table` (
                `id` INT AUTO_INCREMENT PRIMARY KEY
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        sseMessage("📦 Tabela `$table` Criada.");
        usleep(100000); // pausa 0,1 segundos
    }
    // 2️⃣ Obtém colunas atuais
    $stmt = $db->query("SHOW COLUMNS FROM `$table`");
    $existing = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        $existing[$col['Field']] = $col['Type'];
    }

    // 3️⃣ Adiciona ou atualiza colunas que faltam
    foreach ($columns as $name => $definition) {
        if (!array_key_exists($name, $existing)) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `$name` $definition;");
            sseMessage("🟢 Coluna `$name` adicionada.");
            usleep(100000); // pausa 0,1 segundos
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
                usleep(100000); // pausa 0,1 segundos
            }
        }
    }

    // 4️⃣ Remove colunas que não estão mais no schema (exceto id)
    foreach ($existing as $name => $type) {
        if ($name === 'id') continue;
        if (!array_key_exists($name, $columns)) {
            $db->exec("ALTER TABLE `$table` DROP COLUMN `$name`;");
            sseMessage("🔴 Coluna `$name` removida.");
            usleep(100000); // pausa 0,1 segundos
        }
    }
    usleep(100000); // pausa 0,1 segundos
    sseMessage("✅ Tabela `$table` sincronizada!");
}
