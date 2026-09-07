<?php
// Configurações do banco
date_default_timezone_set('America/Araguaina');
$basePath = dirname(__DIR__, 1); // volta duas pastas
require_once $basePath . '/assets/init.php';
require_once $basePath . '/assets/_db/functions/exportData.php';
$host = BD_SERVIDOR;
$dbname = BD_BANCO;
$user = BD_USUARIO;
$pass = BD_SENHA;

$pathOutput = __DIR__ . '/output/'; // pasta onde serão salvos os arquivos

// Cria pasta se não existir
if (!file_exists($pathOutput)) {
    mkdir($pathOutput, 0777, true);
}

// Conecta ao banco
$mysqli = new mysqli($host, $user, $pass, $dbname);
if ($mysqli->connect_error) {
    die("Erro ao conectar: " . $mysqli->connect_error);
}

// Pega todas as tabelas do banco
$tables = [];
$result = $mysqli->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// Função para gerar array de colunas
function getColumns($mysqli, $table) {
    $cols = [];
    $result = $mysqli->query("SHOW COLUMNS FROM `$table`");
    while ($row = $result->fetch_assoc()) {
        $field = $row['Field'];
        $type = $row['Type'];
        $null = $row['Null'] === 'NO' ? 'NOT NULL' : 'DEFAULT NULL';
        $cols[$field] = "$type $null";
    }
    return $cols;
}

// Data atual para arquivo
$date = date('Ymd_His'); // formato 20251022_204512

// Gera arquivos PHP
foreach ($tables as $table) {
    $columns = getColumns($mysqli, $table);
    $phpContent = "<?php\n";
    $phpContent .= "// Arquivo gerado automaticamente em " . date('Y-m-d H:i:s') . "\n";
    $phpContent .= "require_once __DIR__ . '/../functions/syncTable.php';\n\n";
    $phpContent .= "return function(\$db) {\n";
    $phpContent .= "    syncTable(\$db, '$table', [\n";
    foreach ($columns as $field => $definition) {
        $phpContent .= "        '$field' => \"$definition\",\n";
    }
    $phpContent .= "    ]);\n";
    $phpContent .= "};\n";
    $phpContent .= "?>";

    // Data no início do arquivo
    $fileName = $pathOutput . $date . '_' . $table . '.php';
    file_put_contents($fileName, $phpContent);
    echo "Arquivo gerado: $fileName\n";
}

$mysqli->close();
