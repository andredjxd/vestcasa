<?php
include '../assets/_db/db.php';
$cnpjcadas      = trim($_POST['cnpjcadas']);
$cnpjrazao      = trim($_POST['cnpjrazao']);
$sqlInsert ="INSERT INTO `logman3__despesa_empresas`(`cnpj`, `razao_social`) VALUES ('$cnpjcadas','$cnpjrazao')";
$exe = $cnx->query($sqlInsert);
if (!$exe) {
    echo 100; 
}else{
    echo "$cnpjcadas - <b>$cnpjrazao</b>";
}

?>