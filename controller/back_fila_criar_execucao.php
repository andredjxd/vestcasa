<?php
include '../assets/_db/db.php';
header('Content-Type: application/json');

$processo = $_POST['processo'] ?? null;
$data     = $_POST['data'] ?? null;

function formatarData(?string $data): ?string {
    if (!$data) return null;
    $dt = DateTime::createFromFormat('d/m/Y', $data);
    return $dt ? $dt->format('Y-m-d') : null;
}


/* 🔒 Valida processo */
if (!$processo) {
    echo json_encode([
        "success" => false,
        "message" => "PROCESSO NÃO INFORMADO"
    ]);
    exit;
}

/* 🔒 Whitelist de segurança */
$processosPermitidos = [
    'atualizaAvarias.py',
    'atualizaAvariaRelatorio.py',
    'consultaPreco.py',
    'atualizaVenda.py',
    'atualizaInstagram.py'
];

if (!in_array($processo, $processosPermitidos)) {
    echo json_encode([
        "success" => false,
        "message" => "PROCESSO NÃO PERMITIDO"
    ]);
    exit;
}

try {

    /* 🔎 Verifica duplicidade */
    if ($processo === 'consultaPreco.py') {

        $check = $cnx->prepare("
            SELECT id 
            FROM vest_relatorio_fila_execucao 
            WHERE nome_processo = ?
            AND status IN (0,1)
            LIMIT 1
        ");

        $check->bind_param("s", $processo);

    }elseif ($processo === 'atualizaInstagram.py') {

        $check = $cnx->prepare("
            SELECT id
            FROM vest_relatorio_fila_execucao
            WHERE nome_processo = ?
            AND status IN (0,1)
            LIMIT 1
        ");

        $check->bind_param("s", $processo);

    } elseif ($processo === 'atualizaAvariaRelatorio.py') {

        $check = $cnx->prepare("
            SELECT id
            FROM vest_relatorio_fila_execucao
            WHERE nome_processo = ?
            AND status IN (0,1)
            LIMIT 1
        ");

        $check->bind_param("s", $processo);

    } elseif ($processo === 'atualizaVenda.py') {

        $dataFC = formatarData($data);

        $check = $cnx->prepare("
            SELECT id 
            FROM vest_relatorio_fila_execucao 
            WHERE nome_processo = ?
            AND parametros = ?
            AND status IN (0,1)
            LIMIT 1
        ");

        $check->bind_param("ss", $processo, $dataFC);

    } elseif ($processo === 'atualizaAvarias.py') {

        $relAvaria = $data;

        $check = $cnx->prepare("
            SELECT id 
            FROM vest_relatorio_fila_execucao 
            WHERE nome_processo = ?
            AND parametros = ?
            AND status IN (0,1)
            LIMIT 1
        ");

        $check->bind_param("ss", $processo, $relAvaria);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "PROCESSO NÃO PERMITIDO"
        ]);
        exit;
    }

    $check->execute();
    $resultado = $check->get_result();

    if ($resultado->num_rows > 0) {

        echo json_encode([
            "success" => false,
            "message" => $processo === 'atualizaVenda.py'
                ? "PROCESSO JÁ EXISTE PARA ESSA DATA"
                : "PROCESSO JÁ ESTÁ EM EXECUÇÃO"
        ]);
        exit;
    }

    /* 🧾 Insere */
    if ($processo === 'consultaPreco.py') {

        $stmt = $cnx->prepare("
            INSERT INTO vest_relatorio_fila_execucao
            (nome_processo, status)
            VALUES (?, 0)
        ");

        $stmt->bind_param("s", $processo);

    } elseif ($processo === 'atualizaInstagram.py') {

        $stmt = $cnx->prepare("
            INSERT INTO vest_relatorio_fila_execucao
            (nome_processo, status)
            VALUES (?, 0)
        ");

        $stmt->bind_param("s", $processo);

    } elseif ($processo === 'atualizaAvariaRelatorio.py') {

        $stmt = $cnx->prepare("
            INSERT INTO vest_relatorio_fila_execucao
            (nome_processo, status)
            VALUES (?, 0)
        ");

        $stmt->bind_param("s", $processo);

    } elseif ($processo === 'atualizaAvarias.py') {

        $relAvaria = $data;

        $stmt = $cnx->prepare("
            INSERT INTO vest_relatorio_fila_execucao
            (nome_processo, parametros, status)
            VALUES (?, ?, 0)
        ");

        $stmt->bind_param("ss", $processo, $relAvaria);

    } else {

        $dataFC = formatarData($data);

        if (!$dataFC) {
            echo json_encode([
                "success" => false,
                "message" => "PARÂMETRO DATA NÃO INFORMADO"
            ]);
            exit;
        }

        $stmt = $cnx->prepare("
            INSERT INTO vest_relatorio_fila_execucao
            (nome_processo, parametros, status)
            VALUES (?, ?, 0)
        ");

        $stmt->bind_param("ss", $processo, $dataFC);
    }

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Processo adicionado à fila",
        "id_fila" => $stmt->insert_id
    ]);

} catch (mysqli_sql_exception $e) {

    error_log($e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Erro ao criar fila de execução"
    ]);
}