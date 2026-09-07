<?php
include '../assets/_db/db.php';
$dados = [];

$query = "
    SELECT * 
    FROM vest_relatorio_deposito 
    ORDER BY id DESC 
";

$res1 = $cnx->query($query);

if ($res1) {
    $pos = 0;
    while ($row = $res1->fetch_assoc()) {
        $stmt = $cnx->prepare("SELECT SUM(vrdi.valor) AS total, COUNT(vrdi.valor) AS qtd
                                FROM `vest_relatorio_deposito_itens` AS vrdi
                                INNER JOIN vest_relatorio_deposito AS vrd ON vrd.id = vrdi.idrel
                                WHERE vrdi.idrel = ?");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $dad = $result->fetch_assoc();
        $total = $dad['total'];
        $qtd   = $dad['qtd'];

        $pos++;
        if ($row['status'] == 1 ){
            $status = "Finalizado";
        }else{
            $status = "Em Aberto";
        }
        $dados[] = [
            "pos"       => $pos
            ,"id"       => $row['id']
            ,"loja"     => $row['loja']
            ,"dtIni"    => $row['dt_ini']
            ,"dtFim"    => $row['dt_fim']
            ,"total"    => $total
            ,"qtd"      => $qtd
            ,"empresa"  => $row['empresa']
            ,"gvt"      => $row['gvt']
            ,"dtrec"    => $row['dt_rec']
            ,"observ"   => $row['observ']
            ,"status"   => $status
            ,"created"  => $row['created']
                           
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