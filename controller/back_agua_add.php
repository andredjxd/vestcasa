<?php
include '../assets/_db/db.php';

$horario   = $_POST['horario'];
$dataagua  = date('Y-m-d', strtotime(str_replace("/", "-", $_POST['dataagua'])));
$leitura   = $_POST['leitura'];
$nome      = $_POST['nome'];

// Define os campos conforme o horário
$leituraCampo = ($horario == '6') ? 'leitura06' : (($horario == '23') ? 'leitura23' : null);
$nomeCampo    = ($horario == '6') ? 'nome1' : (($horario == '23') ? 'nome2' : null);

if ($leituraCampo && $nomeCampo) {
    // Verifica se já existe um registro para a data
    $sqlVerifica = $cnx->query("SELECT data, $leituraCampo FROM logman3__agua_registro WHERE data = '$dataagua'");

    if ($sqlVerifica->num_rows == 0) {
        // Insere um novo registro se não existir
        $sqlInsert = "INSERT INTO logman3__agua_registro (data, $leituraCampo, $nomeCampo) VALUES ('$dataagua', '$leitura', '$nome')";
        echo ($cnx->query($sqlInsert)) ? "$dataagua - $leitura - <b>$nome</b>" : 100;
    } else {
        // Atualiza o registro existente se o campo estiver vazio
        $leituraVer = $sqlVerifica->fetch_assoc();
        if (empty($leituraVer[$leituraCampo])) {
            $sqlUpdate = "UPDATE logman3__agua_registro SET $leituraCampo = '$leitura', $nomeCampo = '$nome' WHERE data = '$dataagua'";
            $cnx->query($sqlUpdate);
        } else {
            echo 101; // Já existe um valor registrado
        }
    }
}
?>
