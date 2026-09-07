<?php
include '../assets/_db/db.php';
$cqu = isset($_POST['cqu']) ? trim($_POST['cqu']) : null;
$idreal = $_POST['id'];
if($cqu == 100){
    $sqlDelete = "DELETE FROM `logman3__parametros` WHERE id = '".$idreal."'";
    $exe = $cnx->query($sqlDelete);
    if (!$exe) {
        echo 101; 
    }else{
        echo 102;
    }
}elseif($cqu == 101){
    $sqlDelete = "DELETE FROM `logman3__parametros_relatorios` WHERE id = '".$idreal."'";
    $exe = $cnx->query($sqlDelete);
    if (!$exe) {
        echo 101; 
    }else{
        echo 102;
    }
}
?>