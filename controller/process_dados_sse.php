<?php
/**
 * 📄 process_budget_sse.php
 * Processamento de arquivos Excel com feedback em tempo real via SSE
 */

require '../assets/_php/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// ⚙️ Configurações gerais
set_time_limit(0);
ignore_user_abort(true);
ob_implicit_flush(true);
if (ob_get_level() > 0) ob_end_flush();

// 🎯 Cabeçalhos SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// 🔌 Conexão com banco de dados
global $cnx;
if (!isset($cnx)) require '../assets/_php/db_connect.php';

// 📨 Função para enviar mensagens SSE
function sendSSE($data, $event = 'message') {

    echo "event: {$event}\n";

    if (is_array($data)) {
        echo "data: " . json_encode($data) . "\n\n";
    } else {
        echo "data: " . json_encode(["message" => $data]) . "\n\n";
    }

    if (ob_get_level() > 0) {
        ob_flush();
    }

    flush();
}
// 🧮 Função: converter mês/ano em formato de data (YYYY-MM-01)
function tratarMes($mes){
    $meses = ['Jan'=>'01','Fev'=>'02','Mar'=>'03','Abr'=>'04','Mai'=>'05','Jun'=>'06','Jul'=>'07','Ago'=>'08','Set'=>'09','Out'=>'10','Nov'=>'11','Dez'=>'12'];
    $separa = explode('/', $mes);
    return (count($separa) == 2 && isset($meses[$separa[0]]))
        ? "{$separa[1]}-{$meses[$separa[0]]}-01"
        : "0000-00-00";
}
function datadba($d) {

    // Exemplos de uso
    // echo datadba('20/06/2025');  // ➜ 2025-06-20
    // echo datadba('2025-06-20');  // ➜ 2025-06-20
    // echo datadba('20/06/25');    // ➜ 2025-06-20
    // echo datadba('2025/06/20');  // ➜ null (formato inválido)

    if (empty($d) || !is_string($d)) {
        return null;
    }

    $d = trim($d);

    // Detecta formato com base no separador
    if (strpos($d, '/') !== false) {
        // Formato brasileiro: dd/mm/aaaa
        $n = explode('/', $d);
        if (count($n) !== 3) {
            return null;
        }

        $dia = trim($n[0]);
        $mes = trim($n[1]);
        $ano = trim($n[2]);

        if (!ctype_digit($dia) || !ctype_digit($mes) || !ctype_digit($ano)) {
            return null;
        }

        if (strlen($ano) === 2) {
            $ano = ($ano > 50) ? "19$ano" : "20$ano";
        }

        if (!checkdate((int)$mes, (int)$dia, (int)$ano)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $ano, $mes, $dia);

    } elseif (strpos($d, '-') !== false) {
        // Formato de banco: aaaa-mm-dd
        $n = explode('-', $d);
        if (count($n) !== 3) {
            return null;
        }

        $ano = trim($n[0]);
        $mes = trim($n[1]);
        $dia = trim($n[2]);

        if (!ctype_digit($dia) || !ctype_digit($mes) || !ctype_digit($ano)) {
            return null;
        }

        if (!checkdate((int)$mes, (int)$dia, (int)$ano)) {
            return null;
        }

        // Retorna como yyyy-mm-dd (já está certo)
        return sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
    }

    // Formato desconhecido
    return null;
}
function trataValor($va){
    $a = str_replace(".","", $va);
    $b = str_replace(",",".", $a);
    return $b;
}
function datadb($d) {
    $n = explode('/', $d);
    
    // Verifica se a data foi separada corretamente em três partes
    if (count($n) !== 3) {
        return null; // Retorna null se o formato for inválido
    }

    // Garantindo que os valores sejam numéricos e tenham o tamanho correto
    if (!is_numeric($n[0]) || !is_numeric($n[1]) || !is_numeric($n[2]) || 
        strlen($n[0]) != 2 || strlen($n[1]) != 2 || strlen($n[2]) != 4) {
        return null; // Retorna null se o formato estiver incorreto
    }

    return "{$n[2]}-{$n[1]}-{$n[0]}"; // Retorna no formato "aaaammdd"
}
function DataXLS($data){
    if (is_numeric($data)) {
        $resultado = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data)->format('Y-m-d');
        return $resultado;
    } else {
        $resultado = $data; // Caso não seja uma data, pega o valor original
        return $resultado;
    }
}

// 📩 Parâmetros recebidos do frontend
$relatorio = $_GET['nome'] ?? null;
// $relatorio = "RECEBIMENTO";
$dataFiltro =  datadb($_GET['data']) ?? null;
$chamado = $_GET['chamado'] ?? null;

// 🧾 PROCESSO 1: RECEBIMENTO
if ($relatorio === "RECEBIMENTO") {

    $abreArq = "../assets/arquivos/{$relatorio}.xlsx";

    if (!file_exists($abreArq)) {
        sendSSE("Arquivo nao encontrado.", "error");
        echo "event: close\ndata:\n\n";
        exit;
    }

    sendSSE("Iniciando importacao de RECEBIMENTO...{$chamado}", "load");

    if ($excel = SimpleXLSX::parse($abreArq)) {

        $contador = 0;
        $total = count($excel->rows()) - 1; // remove cabeçalho

        $posicao = 1;

        foreach ($excel->rows() as $key => $coluna) {

            if ($key == 0) continue; // pula cabeçalho
            $contador++;

            $loja = trim($coluna[0]);
            $cnpj = trim($coluna[1]);
            $pedido = trim($coluna[2]);
            $nota = trim($coluna[3]);

            $data = DataXLS($coluna[4]);

            $sku = trim($coluna[5]);
            $produto = trim($coluna[6]);
            $marca = trim($coluna[7]);
            $codFornecedor = trim($coluna[8]);
            $codigoBarras = preg_split('/[,;]+/', $coluna[12]);

            /*
            ========================
            CADASTRAR CODIGO BARRAS
            ========================
            */

            foreach ($codigoBarras as $cb){

                $cb = trim($cb);

                if(empty($cb)) continue;

                $cnx->query("
                    INSERT INTO vest_produto_codigo_barras
                    (sku,codigo_barras)
                    VALUES
                    ('$sku','$cb')
                    ON DUPLICATE KEY UPDATE
                    sku = VALUES(sku)
                ");

            }

            $pack = (int)$coluna[9];
            $qtde = (int)str_replace('.', '', $coluna[10]);

            if (empty($sku) || $sku == "SKU") continue;


            /*
            ========================
            CADASTRAR PRODUTO
            ========================
            */

            $cnx->query("
                INSERT INTO vest_produto_cadastro
                (sku,produto,marca,cod_fornecedor)
                VALUES
                ('$sku','$produto','$marca','$codFornecedor')
                ON DUPLICATE KEY UPDATE
                produto = VALUES(produto),
                marca = VALUES(marca)
            ");


            /*
            ========================
            BUSCAR OU CRIAR RECEBIMENTO
            ========================
            */

            $res = $cnx->query("
                SELECT id
                FROM vest_rme_recebimento
                WHERE pedido='$pedido'
                AND nota='$nota'
                LIMIT 1
            ");

            if ($res->num_rows > 0) {

                $rowR = $res->fetch_assoc();
                $recebimentoId = $rowR['id'];

            } else {

                $cnx->query("
                    INSERT INTO vest_rme_recebimento
                    (loja,cnpj,chamado,pedido,nota,data_faturamento,status,data_recebimento)
                    VALUES
                    ('$loja','$cnpj','$chamado','$pedido','$nota','$data',0,'$dataFiltro')
                ");

                $recebimentoId = $cnx->insert_id;
            }

            /*
            ========================
            CRIAR REGISTRO DIVERGENCIA
            ========================
            */

            $cnx->query("
                INSERT INTO vest_rme_recebimento_divergencias
                (recebimento_id,nota,divergencia)
                VALUES
                ('$recebimentoId','$nota',NULL)
                ON DUPLICATE KEY UPDATE
                divergencia = divergencia
            ");

            /*
            ========================
            INSERIR ITEM
            ========================
            */

            $cnx->query("
                INSERT INTO vest_rme_recebimento_itens
                (recebimento_id,sku,pack,qtde_esperada)
                VALUES
                ('$recebimentoId','$sku','$pack','$qtde')
                ON DUPLICATE KEY UPDATE
                qtde_esperada = VALUES(qtde_esperada),
                pack = VALUES(pack)
            ");


            /*
            ========================
            PROGRESSO SSE
            ========================
            */

            $percent = round(($contador / $total) * 100);

            sendSSE(["percent" => $percent], "progress");
            usleep(50000); // 0.05s (opcional)
            
            
        }
        sendSSE(["percent" => 100], "progress");
        sendSSE("Processamento de RECEBIMENTO concluido com sucesso!", "success");
        echo "event: close\ndata:\n\n";
        exit;

    } else {

        sendSSE("Erro ao abrir o arquivo: " . SimpleXLSX::parseError(), "error");
        echo "event: close\ndata:\n\n";
        exit;
    }
}
// ⚠️ PROCESSO DESCONHECIDO
else {
    sendSSE("Processo nao disponivel para '{$relatorio}'.", "error");
    echo "event: close\ndata:\n\n";
}
?>
