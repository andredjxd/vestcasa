<?php
include '../assets/_db/db.php';
$dados = [];

$query = "
        SELECT vrc.id, vrc.codigo, vrc.nome, vrc.admissao, vrc.cpf, vrcf.funcao, vrch.hini, vrch.hfim, vrch.inter, vrc.status
        FROM `vest_relatorio_colaborador` AS vrc
        INNER JOIN vest_relatorio_colab_funcao AS vrcf ON vrcf.id = vrc.idfuncao
        INNER JOIN vest_relatorio_colab_horario AS vrch ON vrch.id = vrc.idhorario;
";

$res1 = $cnx->query($query);

if ($res1) {
    $pos = 0;
    while ($row = $res1->fetch_assoc()) {
        $pos++;
        $dados[] = [
            "id"           => $row['id']
            ,"codigo"      => $row['codigo']
            ,"nome"        => $row['nome']
            ,"admissao"    => $row['admissao']
            ,"cpf"         => $row['cpf']
            ,"idfuncao"    => $row['funcao']
            ,"idhorario"   => $row['hini']. ' - '.$row['hfim']. ' - '.$row['inter']
            ,"status"      => $row['status']

        ];
        
    }
} else {
    $dados = [
        "error" => "103",
        "message" => "Erro ao executar consulta: " . $cnx->error
    ];
}

header('Content-Type: application/json');
echo json_encode($dados, JSON_UNESCAPED_UNICODE);