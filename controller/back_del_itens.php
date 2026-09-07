<?php
include '../assets/_db/db.php';
$idreal = $_POST['id'];
$sqlDelete = "DELETE FROM `logman3__validade_relatorio_item` WHERE id = '".$idreal."'";
$exe = $cnx->query($sqlDelete);
if (!$exe) {
    echo 101; 
}else{
    echo 102;
}

?>