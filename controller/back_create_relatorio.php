<?php
include '../assets/_db/db.php';
$setor      = $_POST['setor'];
$comprador  = $_POST['comprador'];
$nome       = $_POST['nome'];
$sqlInsert ="INSERT INTO `logman3__validade_relatorio`(`setor`, `comprador`, `nome`) 
                    VALUES ('$setor','$comprador','$nome')";
$exe = $cnx->query($sqlInsert);
if (!$exe) {
    echo 100; 
}else{
    echo "$setor - $comprador - <b>$nome</b>";
}

?>