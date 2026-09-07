<?php
include '../assets/_db/db.php';

// Inicializa a variável
$dados = [];

try {
    // Tenta executar a consulta
    $sql = $cnx->query("SELECT * FROM `logman3__parametros`");

    while($dad = $sql->fetch_array()){
        $dados[] = array(
            "id"        => $dad['id'],
            "pos"       => $dad['pos'],
            "ope01"     => $dad['operador01'],
            "dias01"    => $dad['dias01'],
            "ope02"     => $dad['operador02'],
            "dias02"    => $dad['dias02'],
            "setores"   => $dad['setores']
        );
    }

} catch (mysqli_sql_exception $e) {
    // Captura o erro e devolve JSON de erro
    error_log("Erro no SQL: " . $e->getMessage());
    $dados = ["error" => "Tabela 'logman3__parametros' não existe ou erro no SQL."];
}

header('Content-Type: application/json');
echo json_encode($dados);
