<?php
include '../assets/_db/db.php';
$cqu = isset($_POST['cqu']) ? trim($_POST['cqu']) : null;
$pos = trim($_POST['pos']);
$op1 = trim($_POST['op1']);
$dt1 = trim($_POST['dt1']);
$op2 = trim($_POST['op2']);
$dt2 = trim($_POST['dt2']);
$opl = isset($_POST['opl']) ? trim($_POST['opl']) : null;
$sto = isset($_POST['sto']) ? trim($_POST['sto']) : null;
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : null;
$set = trim($_POST['set']);

if($cqu == 100){

    try{
        $sqlInsert ="INSERT INTO `logman3__parametros`
                (`pos`, `operador01`, `dias01`, `operador02`, `dias02`, `setores`) 
                VALUES ('$pos','$op1','$dt1','$op2','$dt2','$set')";
        $exe = $cnx->query($sqlInsert);
    } catch (mysqli_sql_exception $e) {
        // Captura o erro e devolve JSON de erro
        error_log("Erro ao acessar tabela logman3__parametros: " . $e->getMessage());
        $dados = [
            "error" => "100",
            "message" => "Tabela 'logman3__parametros' não encontrada no DB."
    ];
    }
    if (!empty($dados)) {
    $dados = [
        "error" => "102",
        "message" => "Erro ao realizar o cadastro!"
    ];
    header('Content-Type: application/json');
    echo json_encode($dados);
    
    }else{
        $dados = [
        "error" => "101",
        "message" => "Os parâmetros foram adicionados com sucesso!"
    ];
        header('Content-Type: application/json');
        echo json_encode($dados);
    }

}elseif($cqu == 101){

    try{
        $sqlInsert ="INSERT INTO `logman3__parametros_relatorios`
                (`pos`, `nome`, `setor_nome`, `operador01`, `dias01`, `operador02`, `dias02`, `opelogico`, `setores`) 
                VALUES ('$pos','$nom','$sto','$op1','$dt1','$op2','$dt2','$opl','$set')";
        $exe = $cnx->query($sqlInsert);
    } catch (mysqli_sql_exception $e) {
        // Captura o erro e devolve JSON de erro
        error_log("Erro ao acessar tabela logman3__parametros: " . $e->getMessage());
        $dados = [
            "error" => "100",
            "message" => "Tabela 'logman3__parametros_relatorios' não encontrada no DB."
    ];
    }
    if (!empty($dados)) {
    $dados = [
        "error" => "102",
        "message" => "Erro ao realizar o cadastro!"
    ];
    header('Content-Type: application/json');
    echo json_encode($dados);
    
    }else{
        $dados = [
        "error" => "101",
        "message" => "Os parâmetros foram adicionados com sucesso!"
    ];
        header('Content-Type: application/json');
        echo json_encode($dados);
    }

}else{
    $dados = [
        "error" => "103",
        "message" => "Erro geral!"
    ];
        header('Content-Type: application/json');
        echo json_encode($dados);
}

// $sqlInsert ="INSERT INTO `logman3__parametros`(`pos`, `operador01`, `dias01`, `operador02`, `dias02`, `setores`) 
//             VALUES ('$pos','$op1','$dt1','$op2','$dt2','$set')";
// $exe = $cnx->query($sqlInsert);
// if (!$exe) {
//     echo "Erro: " . $cnx->error; 
// }else{
//     echo 101;
// }   

?>