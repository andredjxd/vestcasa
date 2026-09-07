<?php
// Configurações do banco
date_default_timezone_set('America/Araguaina');
$basePath = dirname(__DIR__, 1); // volta duas pastas

require_once $basePath . '/assets/init.php';
$host = BD_SERVIDOR;
$dbname = BD_BANCO;
$user = BD_USUARIO;
$pass = BD_SENHA;

// Conecta ao banco
$mysqli = new mysqli($host, $user, $pass, $dbname);
if ($mysqli->connect_error) {
    die("Erro ao conectar: " . $mysqli->connect_error);
}

$dados = array();
$sql = $mysqli->query("SELECT * FROM vest_produto_codigo_barras");
while($dad = $sql->fetch_array()){

    $dados[]=array(
        "id"=>$dad['id'],
        "sku"=>$dad['sku'],
        "codigobarras"=>$dad['codigo_barras'],
        "status"=>$dad['status']
    );
        
}
header('Content-Type: application/json');
echo json_encode($dados);

$mysqli->close();
?>