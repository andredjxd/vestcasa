<?php
require '../assets/_php/autoload.php'; // Carrega autoload do Composer

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$rel = "HRAP101_TMP4F79"; // Nome do arquivo
$abreArq = "../assets/arquivos/{$rel}.xlsx"; // Caminho do arquivo

if (file_exists($abreArq)) {
    try {
        // Carregar o arquivo Excel
        $spreadsheet = IOFactory::load($abreArq);
        $sheet = $spreadsheet->getActiveSheet();

        // Número total de linhas e colunas
        $totalRows = $sheet->getHighestRow(); // Última linha com dados
        $totalCols = $sheet->getHighestColumn(); // Última coluna com dados (letra)
        $totalColsIndex = Coordinate::columnIndexFromString($totalCols); // Converte para número

        echo "<table border='1' cellspacing='0' cellpadding='5'>";
        
        // Exibir cabeçalho das colunas com letras
        echo "<tr><th>#</th>"; // Adiciona a primeira célula vazia para alinhamento
        for ($col = 1; $col <= $totalColsIndex; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            echo "<th>{$colLetter}</th>";
        }
        echo "</tr>";

        // Exibir dados com número das linhas
        for ($row = 1; $row <= $totalRows; $row++) {
            echo "<tr>";
            echo "<td><b>{$row}</b></td>"; // Exibe o número da linha
            for ($col = 1; $col <= $totalColsIndex; $col++) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $value = $sheet->getCell($colLetter . $row)->getValue();
                echo "<td>{$value}</td>";
            }
            echo "</tr>";
        }

        echo "</table>";
    } catch (Exception $e) {
        echo "Erro ao carregar o arquivo: " . $e->getMessage();
    }
    // Libera memória
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
} else {
    echo "Erro 102: Arquivo não encontrado.";
}
?>

