<?php
date_default_timezone_set('America/Araguaina');

$basePath = dirname(__DIR__, 1);
require_once $basePath . '/assets/init.php';

$mysqli = new mysqli(BD_SERVIDOR, BD_USUARIO, BD_SENHA, BD_BANCO);

if ($mysqli->connect_error) {
    die("Erro ao conectar: " . $mysqli->connect_error);
}

$dados = [];

// 🔍 Busca todos os códigos
$sql = $mysqli->query("
    SELECT codigobarra 
    FROM vest_relatorio_produto_precos
");

while ($row = $sql->fetch_assoc()) {

    $cod = trim($row['codigobarra']);

    // 🔍 Verifica se existe na outra tabela
    $check = $mysqli->query("
        SELECT id, status 
        FROM vest_produto_codigo_barras 
        WHERE TRIM(codigo_barras) = '$cod'
        LIMIT 1
    ");

    if ($check && $check->num_rows > 0) {

        $dadosCheck = $check->fetch_assoc();

        // 🔄 Só atualiza se precisar
        if ($dadosCheck['status'] != 1) {

            $update = $mysqli->query("
                UPDATE vest_produto_codigo_barras 
                SET status = 1 
                WHERE id = {$dadosCheck['id']}
            ");

            $dados[] = [
                "codigobarra" => $cod,
                "acao" => "atualizado"
            ];

        } else {
            $dados[] = [
                "codigobarra" => $cod,
                "acao" => "ja_era_1"
            ];
        }

    } else {
        $dados[] = [
            "codigobarra" => $cod,
            "acao" => "nao_encontrado"
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($dados, JSON_PRETTY_PRINT);

$mysqli->close();
?>