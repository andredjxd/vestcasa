<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

ob_implicit_flush(true);
while (ob_get_level() > 0) {
    ob_end_flush();
}

// Incluir o autoload manual
require '../assets/_php/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;

function dataExcel($valor){

    if(empty($valor)){
        return null;
    }

    // Caso seja número serial do Excel
    if(is_numeric($valor)){
        return Date::excelToDateTimeObject($valor)->format('Y-m-d');
    }

    $valor = trim($valor);

    // Caso venha no formato dd/mm/yyyy ou mm/dd/yyyy
    if(strpos($valor,'/') !== false){

        $p = explode('/',$valor);

        if(count($p) == 3){

            $a = (int)$p[0];
            $b = (int)$p[1];
            $c = (int)$p[2];

            // Se o primeiro número for maior que 12 → dd/mm/yyyy
            if($a > 12){
                return sprintf('%04d-%02d-%02d',$c,$b,$a);
            }

            // Se o segundo número for maior que 12 → mm/dd/yyyy
            if($b > 12){
                return sprintf('%04d-%02d-%02d',$c,$a,$b);
            }

            // padrão brasileiro
            return sprintf('%04d-%02d-%02d',$c,$b,$a);
        }
    }

    // fallback
    return date('Y-m-d', strtotime($valor));
}
function minseg($di,$df){
    $date1 = strtotime($di);
    $date2 = strtotime($df);
    $diff = abs($date2 - $date1);
    // echo $diff."<br>";
    // To get the year divide the resultant date into
    // total seconds in a year (365*60*60*24)
    $years = floor($diff / (365*60*60*24));

    // To get the month, subtract it with years and
    // divide the resultant date into
    // total seconds in a month (30*60*60*24)
    $months = floor(($diff - $years * 365*60*60*24)
                                    / (30*60*60*24));

    // To get the day, subtract it with years and
    // months and divide the resultant date into
    // total seconds in a days (60*60*24)
    $days = floor(($diff - $years * 365*60*60*24 -
                $months*30*60*60*24)/ (60*60*24));

    // To get the hour, subtract it with years,
    // months & seconds and divide the resultant
    // date into total seconds in a hours (60*60)
    $hours = floor(($diff - $years * 365*60*60*24
            - $months*30*60*60*24 - $days*60*60*24)
                                        / (60*60));
    // To get the minutes, subtract it with years,
    // months, seconds and hours and divide the
    // resultant date into total seconds i.e. 60
    $minutes = floor(($diff - $years * 365*60*60*24
    - $months*30*60*60*24 - $days*60*60*24
                        - $hours*60*60)/ 60);

    // To get the minutes, subtract it with years,
    // months, seconds, hours and minutes
    $seconds = floor(($diff - $years * 365*60*60*24
    - $months*30*60*60*24 - $days*60*60*24
            - $hours*60*60 - $minutes*60));

    $tempo = "<br>Tempo decorrido <br>".$minutes." Minutos, ".$seconds." Segundos<br>";
    return $tempo;
}


// $rel = $_POST['nome'];

$rel = "PALMAS";
$chamado = 220050;

sleep(1);

if($rel == 'PALMAS')
{
    $abreArq = "../assets/arquivos/{$rel}.xlsx";

    if(!file_exists($abreArq)){
        sse(["erro"=>"Arquivo não encontrado"]);
        exit;
    }

    try{

        $spreadsheet = IOFactory::load($abreArq);
        $sheet = $spreadsheet->getActiveSheet();

        $totalRows = $sheet->getHighestRow();

        sse(["status"=>"Iniciando importação","total"=>$totalRows]);

        for($row = 2; $row <= $totalRows; $row++){

            $loja = trim($sheet->getCell('A'.$row)->getValue());
            $cnpj = trim($sheet->getCell('B'.$row)->getValue());
            $pedido = trim($sheet->getCell('C'.$row)->getValue());
            $nota = trim($sheet->getCell('D'.$row)->getValue());

            $data = dataExcel($sheet->getCell('E'.$row)->getValue());

            $sku = trim($sheet->getCell('F'.$row)->getFormattedValue());
            $produto = trim($sheet->getCell('G'.$row)->getValue());
            $marca = trim($sheet->getCell('H'.$row)->getValue());
            $codFornecedor = trim($sheet->getCell('I'.$row)->getValue());

            $pack = (int)$sheet->getCell('J'.$row)->getValue();
            $qtde = (int)$sheet->getCell('K'.$row)->getValue();

            if(empty($sku) || $sku == 'SKU'){
                continue;
            }

            /*
            CADASTRAR PRODUTO
            */

            $sqlProduto = "
            INSERT INTO vest_produto_cadastro
            (sku,produto,marca,cod_fornecedor)
            VALUES
            ('$sku','$produto','$marca','$codFornecedor')
            ON DUPLICATE KEY UPDATE
            produto = VALUES(produto),
            marca = VALUES(marca)
            ";

            $cnx->query($sqlProduto);

            /*
            BUSCAR OU CRIAR RECEBIMENTO
            */

            $sqlReceb = "
            SELECT id
            FROM vest_rme_recebimento
            WHERE pedido='$pedido'
            AND nota='$nota'
            LIMIT 1
            ";

            $res = $cnx->query($sqlReceb);

            if($res->num_rows > 0){

                $rowR = $res->fetch_assoc();
                $recebimentoId = $rowR['id'];

            }else{

                $sqlInsert = "
                INSERT INTO vest_rme_recebimento
                (loja,cnpj,chamado,pedido,nota,data_faturamento,status)
                VALUES
                ('$loja','$cnpj','$chamado','$pedido','$nota','$data',0)
                ";

                $cnx->query($sqlInsert);

                $recebimentoId = $cnx->insert_id;
            }

            /*
            INSERIR ITEM
            */

            $sqlItem = "
            INSERT INTO vest_rme_recebimento_itens
            (recebimento_id,sku,pack,qtde_esperada)
            VALUES
            ('$recebimentoId','$sku','$pack','$qtde')
            ON DUPLICATE KEY UPDATE
            qtde_esperada = VALUES(qtde_esperada),
            pack = VALUES(pack)
            ";

            $cnx->query($sqlItem);

            /*
            PROGRESSO SSE
            */

            sse([
                "linha"=>$row,
                "total"=>$totalRows,
                "sku"=>$sku
            ]);
        }

        sse([
            "finalizado"=>true,
            "mensagem"=>"Importação concluída"
        ]);

    }catch(Exception $e){

        sse([
            "erro"=>$e->getMessage()
        ]);
    }

}else{

    sse(["erro"=>"Arquivo inválido"]);
}
?>