<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$idItem       = (int) ($_POST['id_item'] ?? 0);
$codigoAvaria = trim($_POST['codigo_avaria'] ?? '');
$codigoBarras = trim($_POST['codigo_barras'] ?? '');
$tipoAvaria   = trim($_POST['tipoavaria'] ?? ($_POST['tipo_avaria'] ?? ''));
$quantidade   = trim($_POST['quantidade'] ?? '');
$observacao   = trim($_POST['observacao'] ?? '');

if ($idItem <= 0) {
    echo json_encode([
        "error" => 100,
        "message" => "ID do item não informado."
    ]);
    exit;
}

if ($codigoAvaria === '') {
    echo json_encode([
        "error" => 100,
        "message" => "Código da avaria não informado."
    ]);
    exit;
}

if ($codigoBarras === '') {
    echo json_encode([
        "error" => 100,
        "message" => "Código de barras não informado."
    ]);
    exit;
}

if ($tipoAvaria === '') {
    echo json_encode([
        "error" => 100,
        "message" => "Tipo de avaria não informado."
    ]);
    exit;
}

if ($quantidade === '') {
    echo json_encode([
        "error" => 100,
        "message" => "Quantidade não informada."
    ]);
    exit;
}

/**
 * Salva a imagem principal já reduzida/comprimida.
 * Sempre salva em JPG para reduzir tamanho e evitar limite do portal.
 */
function salvarImagemReduzida($origem, $destino, $tipoArquivo, $limiteBytes = 3000000, $larguraMax = 1200, $alturaMax = 1200)
{
    if (!file_exists($origem)) {
        throw new Exception("Arquivo temporário não encontrado.");
    }

    $info = getimagesize($origem);

    if (!$info) {
        throw new Exception("Arquivo não é uma imagem válida.");
    }

    $larguraOriginal = $info[0];
    $alturaOriginal  = $info[1];

    switch ($tipoArquivo) {
        case 'image/jpeg':
            $imagemOriginal = imagecreatefromjpeg($origem);
            break;

        case 'image/png':
            $imagemOriginal = imagecreatefrompng($origem);
            break;

        case 'image/webp':
            $imagemOriginal = imagecreatefromwebp($origem);
            break;

        default:
            throw new Exception("Tipo de imagem não permitido.");
    }

    if (!$imagemOriginal) {
        throw new Exception("Erro ao abrir imagem.");
    }

    $ratio = min(
        $larguraMax / $larguraOriginal,
        $alturaMax / $alturaOriginal,
        1
    );

    $novaLargura = (int) round($larguraOriginal * $ratio);
    $novaAltura  = (int) round($alturaOriginal * $ratio);

    $imagemNova = imagecreatetruecolor($novaLargura, $novaAltura);

    /*
      Fundo branco para PNG/WebP com transparência,
      já que vamos converter tudo para JPG.
    */
    $branco = imagecolorallocate($imagemNova, 255, 255, 255);
    imagefill($imagemNova, 0, 0, $branco);

    imagecopyresampled(
        $imagemNova,
        $imagemOriginal,
        0,
        0,
        0,
        0,
        $novaLargura,
        $novaAltura,
        $larguraOriginal,
        $alturaOriginal
    );

    $qualidade = 82;
    $tamanhoAtual = 0;

    do {
        imagejpeg($imagemNova, $destino, $qualidade);

        clearstatcache(true, $destino);

        $tamanhoAtual = file_exists($destino)
            ? filesize($destino)
            : 0;

        if ($tamanhoAtual > 0 && $tamanhoAtual <= $limiteBytes) {
            break;
        }

        $qualidade -= 5;

    } while ($qualidade >= 55);

    imagedestroy($imagemOriginal);
    imagedestroy($imagemNova);

    if (!file_exists($destino)) {
        throw new Exception("Erro ao salvar imagem reduzida.");
    }

    return filesize($destino);
}

/**
 * Cria thumbnail a partir da imagem principal já reduzida.
 * Também salva em JPG.
 */
function criarThumbnailJpg($origem, $destino, $larguraMax = 300, $alturaMax = 300)
{
    if (!file_exists($origem)) {
        return false;
    }

    $info = getimagesize($origem);

    if (!$info) {
        return false;
    }

    $larguraOriginal = $info[0];
    $alturaOriginal  = $info[1];

    $imagemOriginal = imagecreatefromjpeg($origem);

    if (!$imagemOriginal) {
        return false;
    }

    $proporcao = min(
        $larguraMax / $larguraOriginal,
        $alturaMax / $alturaOriginal,
        1
    );

    $novaLargura = (int) round($larguraOriginal * $proporcao);
    $novaAltura  = (int) round($alturaOriginal * $proporcao);

    if ($novaLargura <= 0 || $novaAltura <= 0) {
        imagedestroy($imagemOriginal);
        return false;
    }

    $thumb = imagecreatetruecolor($novaLargura, $novaAltura);

    $branco = imagecolorallocate($thumb, 255, 255, 255);
    imagefill($thumb, 0, 0, $branco);

    imagecopyresampled(
        $thumb,
        $imagemOriginal,
        0,
        0,
        0,
        0,
        $novaLargura,
        $novaAltura,
        $larguraOriginal,
        $alturaOriginal
    );

    $criado = imagejpeg($thumb, $destino, 75);

    imagedestroy($imagemOriginal);
    imagedestroy($thumb);

    return $criado;
}

function textoMaiusculo($texto)
{
    $texto = trim((string) $texto);

    if (function_exists('mb_strtoupper')) {
        return mb_strtoupper($texto, 'UTF-8');
    }

    return strtoupper($texto);
}

try {

    $cnx->begin_transaction();

    $quantidade = str_replace(',', '.', $quantidade);
    $quantidade = (float) $quantidade;

    /*
      Verifica se já existe outro item com:
      mesma avaria + mesmo código + mesmo tipo
    */
    $stmtDup = $cnx->prepare("
        SELECT id
        FROM vest_avarias_relatorio_itens
        WHERE codigo_avaria = ?
          AND codigo_barras = ?
          AND tipo_avaria = ?
          AND id <> ?
        LIMIT 1
    ");

    $stmtDup->bind_param(
        "sssi",
        $codigoAvaria,
        $codigoBarras,
        $tipoAvaria,
        $idItem
    );

    $stmtDup->execute();
    $resultDup = $stmtDup->get_result();

    if ($resultDup->num_rows > 0) {
        throw new Exception("Já existe outro item com este código de barras e tipo de avaria.");
    }

    $observacaoFinal = textoMaiusculo($observacao);

    $stmt = $cnx->prepare("
        UPDATE vest_avarias_relatorio_itens
        SET 
            codigo_avaria = ?,
            codigo_barras = ?,
            tipo_avaria = ?,
            quantidade = ?,
            observacao = ?,
            updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->bind_param(
        "sssdsi",
        $codigoAvaria,
        $codigoBarras,
        $tipoAvaria,
        $quantidade,
        $observacaoFinal,
        $idItem
    );

    $stmt->execute();

    /*
      Se vierem novas fotos, adiciona ao item
    */
    if (!empty($_FILES['fotos_avaria']['name'][0])) {

        $codigoAvariaLimpo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $codigoAvaria);
        $codigoBarrasLimpo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $codigoBarras);
        $tipoAvariaLimpo   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $tipoAvaria);

        $baseDir = __DIR__ . '/../uploads/avarias/';
        $pastaItem = $baseDir . $codigoAvariaLimpo . '/' . $codigoBarrasLimpo . '/' . $tipoAvariaLimpo . '/';

        if (!is_dir($pastaItem)) {
            mkdir($pastaItem, 0775, true);
        }

        /*
          Aceita JPG, PNG e WEBP, mas salva tudo como JPG.
        */
        $permitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'jpg',
            'image/webp' => 'jpg'
        ];

        foreach ($_FILES['fotos_avaria']['tmp_name'] as $i => $tmpName) {

            // ======================================================
            // DEBUG TEMPORÁRIO UPLOAD FOTO - EDIT
            // ======================================================
            // error_log("========== DEBUG FOTO AVARIA EDIT ==========");
            // error_log("INDEX: " . $i);
            // error_log("NAME: " . ($_FILES['fotos_avaria']['name'][$i] ?? ''));
            // error_log("ERROR: " . ($_FILES['fotos_avaria']['error'][$i] ?? ''));
            // error_log("SIZE: " . ($_FILES['fotos_avaria']['size'][$i] ?? ''));
            // error_log("TMP: " . ($tmpName ?? ''));

            // if (!empty($tmpName) && file_exists($tmpName)) {
            //     error_log("MIME: " . mime_content_type($tmpName));
            // } else {
            //     error_log("TMP NAO EXISTE");
            // }

            // error_log("PASTA ITEM: " . $pastaItem);
            // error_log("PASTA EXISTE: " . (is_dir($pastaItem) ? 'SIM' : 'NAO'));
            // error_log("PASTA GRAVAVEL: " . (is_writable($pastaItem) ? 'SIM' : 'NAO'));
            // error_log("===========================================");

            // ======================================================
            // VALIDA ERRO DO UPLOAD
            // ======================================================
            if (
                empty($tmpName) ||
                !isset($_FILES['fotos_avaria']['error'][$i]) ||
                $_FILES['fotos_avaria']['error'][$i] !== UPLOAD_ERR_OK
            ) {
                error_log(
                    "Erro no upload da foto EDIT | index={$i} | erro=" .
                    ($_FILES['fotos_avaria']['error'][$i] ?? 'SEM_ERRO_INDEX') .
                    " | nome=" .
                    ($_FILES['fotos_avaria']['name'][$i] ?? '')
                );

                continue;
            }

            // ======================================================
            // VALIDA TMP
            // ======================================================
            if (!is_file($tmpName)) {
                error_log(
                    "Arquivo temporário não encontrado EDIT | index={$i} | tmp={$tmpName}"
                );

                continue;
            }

            if (!is_uploaded_file($tmpName)) {
                error_log(
                    "Arquivo não reconhecido como upload HTTP EDIT | index={$i} | tmp={$tmpName}"
                );

                continue;
            }

            // ======================================================
            // VALIDA PASTA
            // ======================================================
            if (!is_dir($pastaItem)) {
                error_log(
                    "Pasta do item não existe no momento de salvar EDIT | pasta={$pastaItem}"
                );

                continue;
            }

            if (!is_writable($pastaItem)) {
                error_log(
                    "Pasta do item sem permissão de escrita EDIT | pasta={$pastaItem}"
                );

                continue;
            }

            // ======================================================
            // MIME
            // ======================================================
            $tipoArquivo = mime_content_type($tmpName);

            if (!array_key_exists($tipoArquivo, $permitidos)) {
                error_log(
                    "MIME não permitido EDIT | mime={$tipoArquivo} | nome=" .
                    ($_FILES['fotos_avaria']['name'][$i] ?? '')
                );

                continue;
            }

            /*
            Agora sempre salva JPG.
            */
            $extensao = 'jpg';

            $nomeArquivo = 'item_' . $idItem . '_' . date('Ymd_His') . '_' . uniqid() . '.' . $extensao;

            $nomeThumb = 'thumb_' . $nomeArquivo;

            $destinoFisico = $pastaItem . $nomeArquivo;
            $destinoThumbFisico = $pastaItem . $nomeThumb;

            $caminhoRelativo = 'uploads/avarias/' .
                $codigoAvariaLimpo . '/' .
                $codigoBarrasLimpo . '/' .
                $tipoAvariaLimpo . '/' .
                $nomeArquivo;

            $caminhoThumbRelativo = 'uploads/avarias/' .
                $codigoAvariaLimpo . '/' .
                $codigoBarrasLimpo . '/' .
                $tipoAvariaLimpo . '/' .
                $nomeThumb;

            $urlArquivo = $caminhoRelativo;

            try {

                /*
                Salva a imagem principal já reduzida.
                Não usa mais move_uploaded_file para manter a foto original pesada.
                */
                $tamanhoArquivo = salvarImagemReduzida(
                    $tmpName,
                    $destinoFisico,
                    $tipoArquivo,
                    3000000,
                    1200,
                    1200
                );

                if (!is_file($destinoFisico)) {
                    error_log(
                        "Imagem principal não foi criada EDIT | destino={$destinoFisico}"
                    );

                    continue;
                }

                /*
                Cria thumbnail para preview rápido a partir da imagem já reduzida.
                */
                $thumbCriado = criarThumbnailJpg(
                    $destinoFisico,
                    $destinoThumbFisico,
                    300,
                    300
                );

                if (!$thumbCriado || !is_file($destinoThumbFisico)) {
                    error_log(
                        "Thumbnail não foi criado EDIT | destino={$destinoThumbFisico}"
                    );

                    $caminhoThumbRelativo = null;
                }

                $tipoArquivoBanco = 'image/jpeg';

                $stmtFoto = $cnx->prepare("
                    INSERT INTO vest_avarias_relatorio_fotos
                    (
                        item_id,
                        codigo_avaria,
                        codigo_barras,
                        nome_arquivo,
                        caminho_arquivo,
                        thumb_arquivo,
                        url_arquivo,
                        tipo_arquivo,
                        tamanho_arquivo
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmtFoto->bind_param(
                    "isssssssi",
                    $idItem,
                    $codigoAvaria,
                    $codigoBarras,
                    $nomeArquivo,
                    $caminhoRelativo,
                    $caminhoThumbRelativo,
                    $urlArquivo,
                    $tipoArquivoBanco,
                    $tamanhoArquivo
                );

                $stmtFoto->execute();

                error_log(
                    "Foto salva com sucesso EDIT | item_id={$idItem} | arquivo={$destinoFisico}"
                );

            } catch (Throwable $e) {

                error_log(
                    "Erro ao processar/salvar foto EDIT | index={$i} | erro=" .
                    $e->getMessage()
                );

                continue;
            }
        }
    }

    $cnx->commit();

    echo json_encode([
        "error" => 101,
        "message" => "Item atualizado com sucesso!",
        "item_id" => $idItem
    ]);

} catch (Throwable $e) {

    try {
        $cnx->rollback();
    } catch (Throwable $rollbackError) {}

    error_log("Erro ao editar item avaria: " . $e->getMessage());

    echo json_encode([
        "error" => 102,
        "message" => $e->getMessage()
    ]);
}
