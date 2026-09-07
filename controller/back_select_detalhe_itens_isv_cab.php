<?php
include '../assets/_db/db.php';
$cqu = isset($_POST['cqu']) ? trim($_POST['cqu']) : null;
$id = $_POST['id'];
// $cqu = 101;
// $id = 9;


$dados = [];
if($cqu == 101){
    $dt_venda = mysqli_fetch_array(mysqli_query($cnx, "SELECT data FROM logman3__smgoi13 ORDER BY data DESC LIMIT 1"));
    
    $stmt = $cnx->prepare("SELECT * FROM `logman3__parametros_relatorios` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dad = $result->fetch_assoc();

    $ope01 = $dad['operador01'];
    $dia01 = (int)$dad['dias01'];
    $ope02 = $dad['operador02'];
    $dia02 = (int)$dad['dias02'];
    $opelogico = strtoupper(trim($dad['opelogico'])); // "IN" ou "NOT IN"
    $setores = $dad['setores']; // Ex: "70,71,72"

    $ISV = mysqli_fetch_array(mysqli_query($cnx, "SELECT SUM(total_estoque) FROM logman3__smgoi13 WHERE nao_vende $ope01 $dia01 AND idade $ope02 $dia02 AND setor_numero $opelogico ($setores)"));
    $ISVvnd = mysqli_fetch_array(mysqli_query($cnx, "SELECT SUM(total_estoque) FROM logman3__smgoi13 WHERE nao_vende $ope01 $dia01 AND idade $ope02 $dia02 AND venda = 'S' AND setor_numero $opelogico ($setores)"));
    $restante = $ISV[0] - $ISVvnd[0];
    $estoque_geral = mysqli_fetch_array(mysqli_query($cnx, "SELECT SUM(total_estoque) FROM logman3__smgoi13 WHERE setor_numero $opelogico ($setores)"));
    $porcent_isv = ($ISV[0] / $estoque_geral[0]) * 100;

    $dados[] = [
        "data"      => $dt_venda[0],
        "dias"      => $dia01,
        "isv"       => (float)$ISV[0],
        "isvnd"     => (float)$ISVvnd[0],
        "restante"  => (float)$restante,
        "estgeral"  => (float)$estoque_geral[0],
        "porcent"   => (float)$porcent_isv
    ];

    header('Content-Type: application/json');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
}
