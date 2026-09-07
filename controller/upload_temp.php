<?php
$targetDir = "../assets/arquivos/";
if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

if (empty($_FILES) || !isset($_FILES['file'])) {
    http_response_code(400);
    echo "Nenhum arquivo enviado.";
    exit;
}

$file = $_FILES['file'];
$filename = $_POST['nome'] ?? $file['name'];
$filename = preg_replace('/[^a-zA-Z0-9_\-\.]/','',$filename);

$targetFile = $targetDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetFile)) {
    echo "Upload realizado com sucesso.";
} else {
    http_response_code(500);
    echo "Erro ao salvar arquivo.";
}
