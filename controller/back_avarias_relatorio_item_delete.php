<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$idItem = (int) ($_POST['id_item'] ?? 0);

if ($idItem <= 0) {
    echo json_encode([
        "error" => 100,
        "message" => "ID do item não informado."
    ]);
    exit;
}

function removerPastasVaziasAteBase($pastaInicial, $pastaBase)
{
    $pastaBase = realpath($pastaBase);

    if (!$pastaBase) {
        return;
    }

    /*
      Se a pasta inicial já não existir, sobe para a pasta pai.
    */
    if (!is_dir($pastaInicial)) {
        $pastaInicial = dirname($pastaInicial);
    }

    $pastaAtual = realpath($pastaInicial);

    if (!$pastaAtual) {
        return;
    }

    /*
      Segurança: só remove dentro da base.
    */
    if (strpos($pastaAtual, $pastaBase) !== 0) {
        return;
    }

    while ($pastaAtual && $pastaAtual !== $pastaBase) {

        if (!is_dir($pastaAtual)) {
            break;
        }

        $itens = array_diff(scandir($pastaAtual), ['.', '..']);

        if (count($itens) > 0) {
            break;
        }

        @rmdir($pastaAtual);

        $pastaPai = dirname($pastaAtual);

        if ($pastaPai === $pastaAtual) {
            break;
        }

        $pastaAtual = realpath($pastaPai);

        if (!$pastaAtual) {
            break;
        }

        if (strpos($pastaAtual, $pastaBase) !== 0) {
            break;
        }
    }
}

function caminhoFisicoSeguro($caminhoRelativo, $baseProjeto, $baseUploadsAvarias)
{
    if (!$caminhoRelativo) {
        return null;
    }

    $caminhoRelativo = trim($caminhoRelativo);
    $caminhoRelativo = ltrim($caminhoRelativo, "/\\");

    $caminhoFisico = $baseProjeto . DIRECTORY_SEPARATOR . str_replace(
        ['/', '\\'],
        DIRECTORY_SEPARATOR,
        $caminhoRelativo
    );

    /*
      Se o arquivo existe, usa realpath para validar.
    */
    if (is_file($caminhoFisico)) {

        $realArquivo = realpath($caminhoFisico);
        $realBase    = realpath($baseUploadsAvarias);

        if (!$realArquivo || !$realBase) {
            return null;
        }

        if (strpos($realArquivo, $realBase) !== 0) {
            return null;
        }

        return $realArquivo;
    }

    /*
      Se não existe, retorna o caminho montado apenas para tentar limpar pasta depois,
      mas ainda validando a pasta pai.
    */
    $pastaPai = dirname($caminhoFisico);

    if (is_dir($pastaPai)) {

        $realPasta = realpath($pastaPai);
        $realBase  = realpath($baseUploadsAvarias);

        if ($realPasta && $realBase && strpos($realPasta, $realBase) === 0) {
            return $caminhoFisico;
        }
    }

    return null;
}

try {

    $cnx->begin_transaction();

    // ======================================================
    // BUSCA O ITEM
    // ======================================================
    $stmtItem = $cnx->prepare("
        SELECT 
            id,
            codigo_avaria,
            codigo_barras,
            tipo_avaria
        FROM vest_avarias_relatorio_itens
        WHERE id = ?
        LIMIT 1
    ");

    $stmtItem->bind_param("i", $idItem);
    $stmtItem->execute();

    $resultItem = $stmtItem->get_result();
    $item = $resultItem->fetch_assoc();

    if (!$item) {
        throw new Exception("Item não encontrado.");
    }

    // ======================================================
    // BUSCA FOTOS DO ITEM
    // ======================================================
    $stmtFotos = $cnx->prepare("
        SELECT 
            id,
            caminho_arquivo,
            thumb_arquivo
        FROM vest_avarias_relatorio_fotos
        WHERE item_id = ?
    ");

    $stmtFotos->bind_param("i", $idItem);
    $stmtFotos->execute();

    $resultFotos = $stmtFotos->get_result();

    $fotos = [];

    while ($foto = $resultFotos->fetch_assoc()) {
        $fotos[] = $foto;
    }

    // ======================================================
    // REMOVE REGISTROS DAS FOTOS
    // ======================================================
    $stmtDelFotos = $cnx->prepare("
        DELETE FROM vest_avarias_relatorio_fotos
        WHERE item_id = ?
    ");

    $stmtDelFotos->bind_param("i", $idItem);
    $stmtDelFotos->execute();

    // ======================================================
    // REMOVE O ITEM
    // ======================================================
    $stmtDelItem = $cnx->prepare("
        DELETE FROM vest_avarias_relatorio_itens
        WHERE id = ?
    ");

    $stmtDelItem->bind_param("i", $idItem);
    $stmtDelItem->execute();

    if ($stmtDelItem->affected_rows <= 0) {
        throw new Exception("Nenhum item foi excluído.");
    }

    // ======================================================
    // COMMIT NO BANCO
    // ======================================================
    $cnx->commit();

    // ======================================================
    // APAGA ARQUIVOS FÍSICOS APÓS COMMIT
    // ======================================================
    $baseProjeto = realpath(__DIR__ . '/../');
    $baseUploadsAvarias = realpath(__DIR__ . '/../uploads/avarias');

    $pastasAfetadas = [];

    if ($baseProjeto && $baseUploadsAvarias) {

        foreach ($fotos as $foto) {

            $caminhosParaApagar = [
                $foto['caminho_arquivo'] ?? '',
                $foto['thumb_arquivo'] ?? ''
            ];

            foreach ($caminhosParaApagar as $caminhoRelativo) {

                $caminhoFisico = caminhoFisicoSeguro(
                    $caminhoRelativo,
                    $baseProjeto,
                    $baseUploadsAvarias
                );

                if (!$caminhoFisico) {
                    continue;
                }

                $pastaArquivo = dirname($caminhoFisico);
                $pastasAfetadas[] = $pastaArquivo;

                if (is_file($caminhoFisico)) {
                    @unlink($caminhoFisico);
                }
            }
        }

        /*
          Remove duplicadas e começa pelas pastas mais profundas.
        */
        $pastasAfetadas = array_unique($pastasAfetadas);

        usort($pastasAfetadas, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        foreach ($pastasAfetadas as $pasta) {
            removerPastasVaziasAteBase($pasta, $baseUploadsAvarias);
        }
    }

    echo json_encode([
        "error" => 101,
        "message" => "Item e fotos excluídos com sucesso.",
        "item_id" => $idItem,
        "codigo_avaria" => $item['codigo_avaria']
    ]);

} catch (Throwable $e) {

    try {
        $cnx->rollback();
    } catch (Throwable $rollbackError) {}

    error_log("Erro ao excluir item avaria: " . $e->getMessage());

    echo json_encode([
        "error" => 102,
        "message" => $e->getMessage()
    ]);
}