<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

// === Função de resposta JSON padronizada ===
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$cqu = isset($_POST['cqu']) ? trim($_POST['cqu']) : null;
$id  = isset($_POST['id'])  ? intval($_POST['id'])  : null;

if (!$cqu || !$id) {
    jsonResponse(['error' => '101', 'message' => 'Parâmetros obrigatórios ausentes (cqu ou id).'], 400);
}

switch ($cqu) {
    case '100':
        $query = "SELECT id, pos, operador01, dias01, operador02, dias02, setores 
                  FROM logman3__parametros 
                  WHERE id = ?";
        break;

    case '101':
        $query = "SELECT id, pos, nome, setor_nome, operador01, dias01, operador02, dias02, opelogico, setores 
                  FROM logman3__parametros_relatorios 
                  WHERE id = ?";
        break;

    default:
        jsonResponse(['error' => '103', 'message' => 'Tipo de consulta inválido.'], 400);
}

$stmt = $cnx->prepare($query);
if (!$stmt) {
    jsonResponse(['error' => '104', 'message' => 'Erro ao preparar a consulta: ' . $cnx->error], 500);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$dados = [];
while ($dad = $result->fetch_assoc()) {
    if ($cqu === '100') {
        $dados[] = [
            "id"      => $dad['id'],
            "pos"     => $dad['pos'],
            "ope01"   => $dad['operador01'],
            "dias01"  => $dad['dias01'],
            "ope02"   => $dad['operador02'],
            "dias02"  => $dad['dias02'],
            "setores" => $dad['setores']
        ];
    } elseif ($cqu === '101') {
        $dados[] = [
            "id"        => $dad['id'],
            "pos"       => $dad['pos'],
            "nome"      => $dad['nome'],
            "setnome"   => $dad['setor_nome'],
            "ope01"     => $dad['operador01'],
            "dias01"    => $dad['dias01'],
            "ope02"     => $dad['operador02'],
            "dias02"    => $dad['dias02'],
            "opelogico" => $dad['opelogico'],
            "setores"   => $dad['setores']
        ];
    }
}

if (empty($dados)) {
    jsonResponse(['error' => '102', 'message' => 'Nenhum registro encontrado.'], 404);
}

jsonResponse($dados);
