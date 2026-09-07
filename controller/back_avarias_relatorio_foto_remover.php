<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$idFoto = (int) ($_POST['id_foto'] ?? 0);

if ($idFoto <= 0) {
    echo json_encode([
        "error" => 100,
        "message" => "ID da foto não informado."
    ]);
    exit;
}

function removerPastasVaziasAteBase($pastaInicial, $pastaBase)
{
    $pastaBase = realpath($pastaBase);

    if (!$pastaBase) {
        return;
    }

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
    // BUSCA FOTO
    // ======================================================
    $stmt = $cnx->prepare("
        SELECT 
            id,
            item_id,
            caminho_arquivo,
            thumb_arquivo
        FROM vest_avarias_relatorio_fotos
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $idFoto);
    $stmt->execute();

    $result = $stmt->get_result();
    $foto = $result->fetch_assoc();

    if (!$foto) {
        throw new Exception("Foto não encontrada.");
    }

    // ======================================================
    // REMOVE REGISTRO DO BANCO
    // ======================================================
    $stmtDel = $cnx->prepare("
        DELETE FROM vest_avarias_relatorio_fotos
        WHERE id = ?
    ");

    $stmtDel->bind_param("i", $idFoto);
    $stmtDel->execute();

    if ($stmtDel->affected_rows <= 0) {
        throw new Exception("Nenhuma foto foi removida.");
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
        "message" => "Foto removida com sucesso.",
        "id_foto" => $idFoto,
        "item_id" => $foto['item_id']
    ]);

} catch (Throwable $e) {

    try {
        $cnx->rollback();
    } catch (Throwable $rollbackError) {}

    error_log("Erro ao remover foto avaria: " . $e->getMessage());

    echo json_encode([
        "error" => 102,
        "message" => $e->getMessage()
    ]);
}