<?php
include '../assets/_db/db.php';

$id = $_POST['id'];
// $id = 8;
$dados = [];

$stmt = $cnx->prepare("
                        SELECT  vrd.*, vrd.id AS idre, vrd.created AS criacao, SUM(vrdi.valor) AS total
                        FROM vest_relatorio_deposito AS vrd
                        LEFT JOIN vest_relatorio_deposito_itens AS vrdi ON vrdi.idrel = vrd.id
                        WHERE vrd.id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    $dados = [
        "id"      => $row['idre'] ,
        "loja"    => $row['loja'],
        "dtIni"   => $row['dt_ini'],
        "dtFim"   => $row['dt_fim'],
        "total"   => $row['total'] ?? "0.00",
        "status"  => ($row['status'] == 1 ? "Finalizado" : "Em Aberto"),
        "created" => $row['criacao'] ?? 0
    ];

} else {
    $dados = [
        "error"   => "404",
        "message" => "Registro não encontrado"
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($dados, JSON_UNESCAPED_UNICODE);
?>