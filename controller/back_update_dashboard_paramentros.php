<?php
include '../assets/_db/db.php';
$cqu = isset($_POST['cqu']) ? trim($_POST['cqu']) : null;
$id  = $_POST['id'];
$pos = $_POST['pos'];
$op1 = $_POST['op1'];
$dt1 = $_POST['dt1'];
$op2 = $_POST['op2'];
$dt2 = $_POST['dt2'];
$set = $_POST['set'];
$opl = isset($_POST['opl']) ? trim($_POST['opl']) : '';
$sto = isset($_POST['sto']) ? trim($_POST['sto']) : '';
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    // $id = 1;
    // $pos = 1;
    // $op1 = '>=';
    // $dt1 = 7;
    // $op2 = '>=';
    // $dt2 = 7;
    // $set = '70,71,72,73,100';
if($cqu == 100){
    // Query preparada
    $stmt = $cnx->prepare("
        UPDATE `logman3__parametros` 
        SET `pos` = ?, 
            `operador01` = ?, 
            `dias01` = ?, 
            `operador02` = ?, 
            `dias02` = ?, 
            `setores` = ?
        WHERE `id` = ?
    ");

    // Bind dos parâmetros
    $stmt->bind_param("ssssssi", $pos, $op1, $dt1, $op2, $dt2, $set, $id);

    // Execução
    if ($stmt->execute()) {
        echo 101;
    } else {
        echo "Erro: " . $cnx->error;
    }
}elseif($cqu == 101){
    // Query preparada
    $stmt = $cnx->prepare("
        UPDATE `logman3__parametros_relatorios` 
        SET `pos` = ?,
            `nome` = ?, 
            `setor_nome` = ?, 
            `operador01` = ?, 
            `dias01` = ?, 
            `operador02` = ?, 
            `dias02` = ?, 
            `opelogico` = ?, 
            `setores` = ?
        WHERE `id` = ?
    ");

    // Bind dos parâmetros
    $stmt->bind_param("sssssssssi", $pos, $nom, $sto, $op1, $dt1, $op2, $dt2, $opl, $set, $id);

    // Execução
    if ($stmt->execute()) {
        echo 101;
    } else {
        echo "Erro: " . $cnx->error;
    }
}
?>
