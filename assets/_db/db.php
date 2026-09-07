<?php
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

$basePath = dirname(__DIR__, 2); // volta duas pastas
require_once $basePath . '/assets/init.php';
require_once $basePath . '/assets/_php/auth_session.php';

$scriptAtual = basename($_SERVER['SCRIPT_FILENAME']);
if (!in_array($scriptAtual, ['back_login.php', 'back_logout.php'], true)) {
    exigirLoginApi();
}

// Conexão com o banco de dados
$cnx = new mysqli(BD_SERVIDOR, BD_USUARIO, BD_SENHA, BD_BANCO);

// Verificar conexão
if ($cnx->connect_error) {
    die("Falha na conexão: " . $con->connect_error);
}

// Definir charset corretamente
mysqli_set_charset($cnx, 'utf8mb4');

// Conexão com o banco de dados MySQL usando PDO
try {
    $pdo = new PDO("mysql:host=".BD_SERVIDOR.";dbname=".BD_BANCO.";charset=utf8mb4"
    ,BD_USUARIO
    ,BD_SENHA);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Erro na conexão: ' . $e->getMessage();
    exit;
}

?>