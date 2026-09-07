<?php
require '../assets/_php/autoload.php'; // Carrega autoload do Composer

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$rel = "PALMAS"; // Nome do arquivo
$abreArq = "../assets/arquivos/{$rel}.xlsx"; // Caminho do arquivo

$buscar = "MEGAVEST - PALMAS - TEMP"; // Texto que queremos filtrar

if (file_exists($abreArq)) {
    try {

        $spreadsheet = IOFactory::load($abreArq);
        $sheet = $spreadsheet->getActiveSheet();

        $totalRows = $sheet->getHighestRow();
        $totalCols = $sheet->getHighestColumn();
        $totalColsIndex = Coordinate::columnIndexFromString($totalCols);

        $dadosImportacao = [];

        echo "<table border='1' cellspacing='0' cellpadding='5'>";

        echo "<tr><th>#</th>";
        for ($col = 1; $col <= $totalColsIndex; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            echo "<th>{$colLetter}</th>";
        }
        echo "</tr>";

        for ($row = 1; $row <= $totalRows; $row++) {

            $linha = [];
            $encontrou = false;

            for ($col = 1; $col <= $totalColsIndex; $col++) {

                $colLetter = Coordinate::stringFromColumnIndex($col);
                $value = $sheet->getCell($colLetter . $row)->getValue();

                $linha[$colLetter] = $value;

                if (stripos($value, $buscar) !== false) {
                    $encontrou = true;
                }
            }

            // somente linhas com MEGAVEST - PALMAS - TEMP
            if ($encontrou) {

                $dadosImportacao[] = $linha;

                echo "<tr>";
                echo "<td><b>{$row}</b></td>";

                foreach ($linha as $value) {
                    echo "<td>{$value}</td>";
                }

                echo "</tr>";
            }
        }

        echo "</table>";

        // Debug para ver estrutura pronta para banco
        echo "<pre>";
        print_r($dadosImportacao);
        echo "</pre>";

    } catch (Exception $e) {
        echo "Erro ao carregar o arquivo: " . $e->getMessage();
    }
} else {
    echo "Erro 102: Arquivo não encontrado.";
}
?>