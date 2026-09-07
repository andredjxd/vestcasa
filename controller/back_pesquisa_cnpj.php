<?php
include '../assets/_db/db.php';
$dados = array();
$nome = $_POST['name'];
$sql = $cnx->query("SELECT * FROM logman3__despesa_empresas
                    WHERE cnpj LIKE '".$nome."%' OR razao_social LIKE '".$nome."%'");
while($dad = $sql->fetch_array(MYSQLI_BOTH)){
    $dados[]=array(
        "value"=>$dad['cnpj'].' | '.$dad['razao_social'],
        "id"=>$dad['id'],
        "cnpj"=>$dad['cnpj'],
        "razao"=>$dad['razao_social']
        );
}
echo json_encode($dados);
?>