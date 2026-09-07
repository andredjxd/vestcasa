<?php
include '../assets/_db/db.php';

/**
 * Retorna a data mais recente de uma tabela, formatada, ou "Tabela Vazia" se não houver dados.
 */
function getUltimaData($conexao, $tabela, $coluna, $filial = null) {
    if ($filial !== null) {
        $sql = $conexao->prepare("SELECT $coluna FROM `$tabela` WHERE filial = ? ORDER BY $coluna DESC LIMIT 1");
        $sql->bind_param('s', $filial);
    } else {
        $sql = $conexao->prepare("SELECT $coluna FROM `$tabela` ORDER BY $coluna DESC LIMIT 1");
    }

    $sql->execute();
    $resultado = $sql->get_result()->fetch_array(MYSQLI_ASSOC);
    return $resultado ? date('d/m/Y', strtotime($resultado[$coluna])) : "Tabela Vazia";
}

/**
 * Retorna todas as datas distintas (sem repetição) de uma tabela,
 * ordenadas da mais recente para a mais antiga.
 */
function getDatasDistintas($conexao, $tabela, $coluna) {
    // Evita injeção em nomes de tabela/coluna
    $tabela = preg_replace('/[^a-zA-Z0-9_]/', '', $tabela);
    $coluna = preg_replace('/[^a-zA-Z0-9_]/', '', $coluna);

    $sql = $conexao->prepare("SELECT DISTINCT `$coluna` FROM `$tabela` WHERE `$coluna` IS NOT NULL ORDER BY `$coluna` DESC");
    $sql->execute();
    $resultado = $sql->get_result();

    $datas = [];
    while ($row = $resultado->fetch_assoc()) {
        $datas[] = date('d/m/Y', strtotime($row[$coluna]));
    }

    $sql->close();
    return $datas ?: ["Tabela Vazia"];
}

// Defina aqui a filial desejada (exemplo: 3)
$f702 = 702;
$f671 = 671;

$dados[] = array(
    "smg13"        => getUltimaData($cnx, 'logman3__smgoi13', 'data'),
    "srt03"        => getUltimaData($cnx, 'logman3__srtbi03', 'data'),
    "svdbi62"      => getUltimaData($cnx, 'logman3__svdbi62', 'data'),
    "svdbia2"      => getUltimaData($cnx, 'logman3__svdbia2', 'data'),
    "saebidiario"  => getUltimaData($cnx, 'logman3__saeoi51_history', 'dataeve'),
    "smgoi12"      => getUltimaData($cnx, 'logman3__smgoi12', 'data', $f702),
    "smgoi12out"   => getUltimaData($cnx, 'logman3__smgoi12', 'data', $f671),
    "vndrub"       => getDatasDistintas($cnx, 'logman3__smgoi13', 'dtvnd')
    
);

echo json_encode($dados);
