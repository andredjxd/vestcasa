<?php
include '../assets/_db/db.php';
header('Content-Type: application/json');

$idreal = $_POST['id'];
// $tabela = 'vest_relatorio_colaborador';

// $stmt = $cnx->prepare("UPDATE $tabela SET status = 1 WHERE id = ?");
// $stmt->bind_param("i", $idreal);

$tabela = $_POST['tabela']; // exemplo se vier via POST

// 🔒 Lista de tabelas permitidas
$tabelasPermitidas = [
    'vest_relatorio_colaborador',
    'vest_relatorio_fechamento'
];

if (!in_array($tabela, $tabelasPermitidas)) {
    die(json_encode([
        "success" => false,
        "message" => "Tabela inválida"
    ]));
}

// Monta a query com a tabela validada
$sql = "UPDATE $tabela SET status = 1 WHERE id = ?";

$stmt = $cnx->prepare($sql);
$stmt->bind_param("i", $idreal);

if (!$stmt->execute()) {
    echo json_encode([
        "error" => true,
        "message" => "Erro ao atualizar registro"
    ]);
} else {
    echo json_encode([
        "error" => false,
        "message" => "Registro atualizado com sucesso",
        "rows_affected" => $stmt->affected_rows
    ]);
}

$stmt->close();
$cnx->close();
?>
