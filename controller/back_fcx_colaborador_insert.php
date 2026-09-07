<?php
header('Content-Type: application/json');

include '../assets/_db/db.php';

$dados = $_POST['dados'] ?? [];

$codigo   = isset($dados['codigo']) ? (int)$dados['codigo'] : null;
$nome     = $dados['nome']    ?? null;
$dtadmis  = $dados['dtadmis'] ?? null;
$cpf      = $dados['cpf']     ?? null;
$funcao   = isset($dados['funcao'])  ? (int)$dados['funcao']  : null;
$horario  = isset($dados['horario']) ? (int)$dados['horario'] : null;

function formatarData(?string $data): ?string {
    if (!$data) return null;
    $dt = DateTime::createFromFormat('d/m/Y', $data);
    return $dt ? $dt->format('Y-m-d') : null;
}

$dataadm = formatarData($dtadmis);

try {

    $stmt = $cnx->prepare("
        INSERT INTO vest_relatorio_colaborador
        (codigo, nome, admissao, cpf, idhorario, idfuncao)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception('Erro ao preparar SQL');
    }

    $stmt->bind_param(
        "isssii",
        $codigo,
        $nome,
        $dataadm,
        $cpf,
        $horario,
        $funcao
    );

    $stmt->execute();

    echo json_encode([
        "error"     => 101,
        "message"   => "Cadastro realizado com sucesso",
        "id_insert" => $stmt->insert_id
    ]);

} catch (Throwable $e) {

    error_log($e->getMessage());

    echo json_encode([
        "error"   => true,
        "message" => "Erro ao salvar no banco de dados"
    ]);
}
?>