<?php
include '../assets/_db/db.php';
$loja       = trim($_POST['nomeloja']);
$inicial = trim($_POST['dataincial'] ?? '');
$final   = trim($_POST['datafinal'] ?? '');

function formatarData(?string $data): ?string
{
    if (!$data) {
        return null;
    }

    $dt = DateTime::createFromFormat('d/m/Y', $data);

    return $dt ? $dt->format('Y-m-d') : null;
}

$dataInicial = formatarData($inicial);
$dataFinal   = formatarData($final);

header('Content-Type: application/json');

if ($dataInicial && $dataFinal && $dataInicial > $dataFinal) {
    echo json_encode([
        "error"   => "100",
        "message" => "Data inicial não pode ser maior que a final!"
    ]);
    // echo 'Data inicial não pode ser maior que a final';
}else{
    try {

        // Ativa exceções do mysqli (IMPORTANTE)
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $stmt = $cnx->prepare(
            "INSERT INTO vest_relatorio_deposito (loja, dt_ini, dt_fim, status)
            VALUES (?, ?, ?, ?)"
        );

        $status = 0;
        $stmt->bind_param("sssi", $loja, $dataInicial, $dataFinal, $status);
        $stmt->execute();

        echo json_encode([
            "error"   => "101",
            "message" => "Relatório criado com sucesso!"
        ]);

    } catch (mysqli_sql_exception $e) {

        error_log("Erro vest_relatorio_deposito: " . $e->getMessage());

        echo json_encode([
            "error"   => "102",
            "message" => "Erro ao realizar o cadastro."
        ]);
    }
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