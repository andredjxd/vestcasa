<?php
include '../assets/_db/db.php';
$id     = $_POST['id'];
$seto   = $_POST['setor'];
$comp   = $_POST['comprador'];
$nome   = $_POST['nome'];
// $cod = 26780;
$sqlup = "UPDATE `logman3__validade_relatorio` 
                        SET `setor`='$seto',`comprador`='$comp',`nome`='$nome'
                        WHERE id = ".$id." ";
$exeup = $cnx->query($sqlup);

if (!$exeup) {
    echo 102; 
}else{
    echo 101;
}

?>
