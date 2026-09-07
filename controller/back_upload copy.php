<?php
include '../assets/_db/db.php';
if(!empty($_FILES)){ 
    // File path configuration 
    $uploadDir = "../assets/arquivos/"; 
    $fileName = basename($_FILES['file']['name']);
    //$new = 'SRTBI13.csv';
    $uploadFilePath = $uploadDir.$fileName;
    if(!move_uploaded_file($_FILES['file']['tmp_name'], $uploadFilePath)){
        echo "Erro ao realizar upload do arquivo "+$fileName;
    }; 
};
?>