<?php
include '../assets/_db/db.php';
$codQuery = $_POST['codquery'];
// Inicializa a variável
$dados = [];
if ($codQuery == 100){
    $sql = $cnx->query("SELECT pos FROM `logman3__parametros`");
    while($dad = $sql->fetch_array()){
        $dados[] = [
            "pos" => $dad['pos']
        ];    
    }

    // Retorna JSON
    header('Content-Type: application/json');
    echo json_encode($dados);
}elseif($codQuery == 101){
    $sql = $cnx->query("SELECT pos FROM `logman3__parametros_relatorios`");
    while($dad = $sql->fetch_array()){
        $dados[] = [
            "pos" => $dad['pos']
        ];    
    }

    // Retorna JSON
    header('Content-Type: application/json');
    echo json_encode($dados);
}
?>
