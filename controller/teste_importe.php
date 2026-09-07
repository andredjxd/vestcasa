<?php
require '../assets/_php/autoload.php'; // Carrega autoload do Composer
use PhpOffice\PhpSpreadsheet\IOFactory;

function tratarMes($mes) {
    $meses = [
        'Jan' => '01', 'Fev' => '02', 'Mar' => '03', 'Abr' => '04',
        'Mai' => '05', 'Jun' => '06', 'Jul' => '07', 'Ago' => '08',
        'Set' => '09', 'Out' => '10', 'Nov' => '11', 'Dez' => '12'
    ];

    $separa = explode('/', $mes);
    return (count($separa) == 2 && isset($meses[$separa[0]])) 
        ? "{$separa[1]}-{$meses[$separa[0]]}-01" 
        : "Formato inválido.";
}

$rel = "90"; // Nome do arquivo
$abreArq = "../assets/arquivos/{$rel}.xlsx"; // Caminho do arquivo

if (!file_exists($abreArq)) {
    die("Erro 102: Arquivo não encontrado.");
}

try {
    $cnx->query("TRUNCATE TABLE logman3__budget"); // Limpa a tabela antes de inserir novos dados
    $cnx->query("TRUNCATE TABLE logman3__budget_vendas"); // Limpa a tabela antes de inserir novos dados
    $spreadsheet = IOFactory::load($abreArq);
    $sheet = $spreadsheet->getActiveSheet();

    $totalRows = $sheet->getHighestRow();
    // index das colunas ['Data Mês'],['Ano Anterior'],['Previsto'],['Realizado']
    $colunasMeses = [
        'Jan' => ['G4', 'D', 'G', 'J']
        ,'Fev' => ['T4', 'Q', 'T', 'W']
        ,'Mar' => ['AG4', 'AD', 'AG', 'AJ']
        ,'Abr' => ['AT4', 'AQ', 'AT', 'AW']
        ,'Mai' => ['BG4', 'BD', 'BG', 'BJ']
        ,'Jun' => ['BT4', 'BQ', 'BT', 'BW']
        ,'Jul' => ['CG4', 'CD', 'CG', 'CJ']
        ,'Ago' => ['CT4', 'CQ', 'CT', 'CW']
        ,'Set' => ['DG4', 'DD', 'DG', 'DJ']
        ,'Out' => ['DT4', 'DQ', 'DT', 'DW']
        ,'Nov' => ['EG4', 'ED', 'EG', 'EJ']
        ,'Dez' => ['ET4', 'EQ', 'ET', 'EW']
    ];

    foreach ($colunasMeses as $mes => $colunas) {
        $datasMeses[$mes] = tratarMes($sheet->getCell($colunas[0])->getFormattedValue());
    }
    //echo "<table border='1' cellspacing='0' cellpadding='5'>";
    //echo "<tr><th>#</th><th>Linha</th><th>Dados</th></tr>";
    $c = 1;
    for ($row = 17; $row <= $totalRows; $row++) {
        $PegaRol = $sheet->getCell('B' . $row)->getValue();
        $Rol = explode(' ', $PegaRol);

        if ($PegaRol == 'Venda Bruta') {
            foreach ($colunasMeses as $mes => $colunas) {
                [$colData, $ColAnteriorVenda, $ColPrevistoVenda, $ColRealizadoVenda] = $colunas;

                // Obtém os valores das células, tratando valores nulos
                $valor1 = $sheet->getCell($ColAnteriorVenda . $row)->getValue() ?? 'N/A';
                $valor2 = $sheet->getCell($ColPrevistoVenda . $row)->getValue() ?? 'N/A';
                $valor3 = $sheet->getCell($ColRealizadoVenda . $row)->getValue() ?? 'N/A';

                echo "{$datasMeses[$mes]}: Anterior: $valor1 - Previsto: $valor2 - Realizado: $valor3<br>";
                $query = "INSERT INTO `logman3__budget_vendas`(`tipo`, `data`, `anterior`, `previsto`, `realizado`) 
                            VALUES ('venda', '{$datasMeses[$mes]}','$valor1','$valor2','$valor3')";
                $cnx->query($query);
            }
        }elseif($PegaRol == 'Recurring Operating Result'){
            foreach ($colunasMeses as $mes => $colunas) {
                [$colData, $ColAnteriorVenda, $ColPrevistoVenda, $ColRealizadoVenda] = $colunas;

                // Obtém os valores das células, tratando valores nulos
                $valor1 = $sheet->getCell($ColAnteriorVenda . $row)->getValue() ?? 'N/A';
                $valor2 = $sheet->getCell($ColPrevistoVenda . $row)->getValue() ?? 'N/A';
                $valor3 = $sheet->getCell($ColRealizadoVenda . $row)->getValue() ?? 'N/A';

                echo "{$datasMeses[$mes]}: ROR Anterior: $valor1 - ROR Previsto: $valor2 - ROR Realizado: $valor3<br>";
                $query = "INSERT INTO `logman3__budget_vendas`(`tipo`, `data`, `anterior`, `previsto`, `realizado`) 
                            VALUES ('ror', '{$datasMeses[$mes]}','$valor1','$valor2','$valor3')";
                $cnx->query($query);
            }
        }

        // var_dump(trim($PegaRol));
        // echo $PegaRol.'<br>';

        if (!isset($Rol[0]) || !is_numeric($Rol[0])) continue;

        foreach ($colunasMeses as $mes => $colunas) {
            [$colData, $colAnterior, $colPrevisto, $colRealizado] = $colunas;
            $DadosAnterior = $sheet->getCell($colAnterior . $row)->getValue();
            $DadosPrevisto = $sheet->getCell($colPrevisto . $row)->getValue();
            $DadosRealizado = $sheet->getCell($colRealizado . $row)->getValue();

            // Exibir os dados na tabela
            //echo "<tr><td>{$c}</td><td>{$row}</td><td>{$Rol[0]} - {$datasMeses[$mes]} - {$DadosAnterior} - {$DadosPrevisto} - {$DadosRealizado}</td></tr>";
            $c++;

            // Inserir no banco de dados
            $query = "INSERT INTO `logman3__budget` (`rol`, `nome`, `data`, `realizadoanterior`, `previsto`, `realizado`) 
                      VALUES ('$Rol[0]', '$PegaRol', '{$datasMeses[$mes]}', '$DadosAnterior', '$DadosPrevisto', '$DadosRealizado')";
            $cnx->query($query);
        }
    }
    $cnx->close();
    //echo "</table>";

} catch (Exception $e) {
    echo "Erro ao carregar o arquivo: " . $e->getMessage();
}
?>
