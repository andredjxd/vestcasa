<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

// ================================
// 📥 Receber e validar codigos
// ================================
$codigosRaw = $_POST['codigos'] ?? null;

if (!$codigosRaw) {
    echo json_encode(["erro" => "Nenhum código informado"]);
    exit;
}

$codigos = json_decode($codigosRaw, true);

if (!is_array($codigos) || !count($codigos)) {
    echo json_encode(["erro" => "Lista de códigos inválida"]);
    exit;
}

$dados = [];

foreach ($codigos as $codigoDigitado) {

    $codigoLimpo = preg_replace('/\D/', '', (string) $codigoDigitado);

    if (!$codigoLimpo) {
        $dados[] = [
            "codigo_digitado" => $codigoDigitado,
            "encontrado" => false
        ];

        $sqlUpd = $cnx->prepare("UPDATE vest_etiquetas_gerador SET status = 'nao_encontrado' WHERE codigo_barras = ?");
        $sqlUpd->bind_param("s", $codigoDigitado);
        $sqlUpd->execute();
        $sqlUpd->close();

        continue;
    }

    // ================================
    // 🔹 1) Tenta achar direto pelo codigobarra atual
    // ================================
    $sql = $cnx->prepare("
        SELECT sku, codigobarra, descricao,
               preco_clube, limitacao_clube,
               preco_max, limitacao_max,
               preco_varejo, limitacao_varejo,
               updated
        FROM vest_relatorio_produto_precos
        WHERE codigobarra = ?
        LIMIT 1
    ");
    $sql->bind_param("s", $codigoLimpo);
    $sql->execute();
    $row = $sql->get_result()->fetch_assoc();
    $sql->close();

    // ================================
    // 🔹 2) Se não achou, resolve o SKU pelo cadastro de
    // codigos de barra (pode ser um codigo antigo/inativo)
    // ================================
    if (!$row) {

        $sqlSku = $cnx->prepare("
            SELECT sku
            FROM vest_produto_codigo_barras
            WHERE codigo_barras = ?
            LIMIT 1
        ");
        $sqlSku->bind_param("s", $codigoLimpo);
        $sqlSku->execute();
        $rowSku = $sqlSku->get_result()->fetch_assoc();
        $sqlSku->close();

        if ($rowSku) {

            $sql = $cnx->prepare("
                SELECT sku, codigobarra, descricao,
                       preco_clube, limitacao_clube,
                       preco_max, limitacao_max,
                       preco_varejo, limitacao_varejo,
                       updated
                FROM vest_relatorio_produto_precos
                WHERE sku = ?
                LIMIT 1
            ");
            $sql->bind_param("s", $rowSku['sku']);
            $sql->execute();
            $row = $sql->get_result()->fetch_assoc();
            $sql->close();
        }
    }

    if (!$row) {
        $dados[] = [
            "codigo_digitado" => $codigoDigitado,
            "encontrado" => false
        ];

        $sqlUpd = $cnx->prepare("UPDATE vest_etiquetas_gerador SET status = 'nao_encontrado' WHERE codigo_barras = ?");
        $sqlUpd->bind_param("s", $codigoDigitado);
        $sqlUpd->execute();
        $sqlUpd->close();

        continue;
    }

    // ================================
    // 🔹 3) Garante que o codigo de barra impresso na
    // etiqueta seja o que estiver ATIVO no banco
    // ================================
    $codigoAtivo = $row['codigobarra'];

    $sqlAtivo = $cnx->prepare("
        SELECT codigo_barras
        FROM vest_produto_codigo_barras
        WHERE sku = ?
        ORDER BY (status = 1) DESC
        LIMIT 1
    ");
    $sqlAtivo->bind_param("s", $row['sku']);
    $sqlAtivo->execute();
    $rowAtivo = $sqlAtivo->get_result()->fetch_assoc();
    $sqlAtivo->close();

    if ($rowAtivo) {
        $codigoAtivo = $rowAtivo['codigo_barras'];
    }

    $dados[] = [
        "codigo_digitado" => $codigoDigitado,
        "encontrado" => true,
        "sku" => $row['sku'],
        "codigobarra" => $codigoAtivo,
        "descricao" => $row['descricao'],

        "preco_clube" => (float) $row['preco_clube'],
        "limitacao_clube" => (int) $row['limitacao_clube'],

        "preco_max" => (float) $row['preco_max'],
        "limitacao_max" => (int) $row['limitacao_max'],

        "preco_varejo" => (float) $row['preco_varejo'],

        "data_registro" => $row['updated']
    ];

    $sqlUpd = $cnx->prepare("UPDATE vest_etiquetas_gerador SET status = 'encontrado', descricao = ? WHERE codigo_barras = ?");
    $sqlUpd->bind_param("ss", $row['descricao'], $codigoDigitado);
    $sqlUpd->execute();
    $sqlUpd->close();
}

// ================================
// 🔒 Fechamento
// ================================
$cnx->close();

echo json_encode($dados);
