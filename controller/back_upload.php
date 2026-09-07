<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../assets/_db/db.php';

if (!empty($_FILES)) {

    $uploadDir = __DIR__ . '/../assets/arquivos/';

    // 🔍 Verifica se a pasta existe
    if (!is_dir($uploadDir)) {
        die("Pasta não existe: " . $uploadDir);
    }

    // 🔐 Nome do arquivo
    $fileName = basename($_FILES['file']['name']);

    $uploadFilePath = $uploadDir . $fileName;

    // 🚨 Debug
    if ($_FILES['file']['error'] !== 0) {
        die("Erro no upload: " . $_FILES['file']['error']);
    }

    // 🚀 Move arquivo
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadFilePath)) {
        die("Erro ao mover arquivo para: " . $uploadFilePath);
    }

    echo "Upload realizado com sucesso!";
}