<?php
include '../assets/_db/db.php';
$cnpjdesp   = $_POST['cnpjdesp'];
$razsocial  = $_POST['razsocial'];
$nfedesp    = $_POST['nfedesp'];
$nfeserie   = $_POST['nfeserie'];
$descdesp   = $_POST['descdesp'];

$sqlInsert ="INSERT INTO `logman3__despesa`(`cnpj`, `razao_social`, `nfe_n`, `nfe_s`, `descricao_despesa`) 
                VALUES ('$cnpjdesp','$razsocial','$nfedesp','$nfeserie','$descdesp')";
$exe = $cnx->query($sqlInsert);
if (!$exe) {
    echo 100; 
}else{
    echo "$cnpjdesp - $razsocial - <b>$nfedesp</b>";
}

?>