<?php
include '../assets/_db/db.php';

$ini = $_POST['ini'];
$fim = $_POST['fim'];

// $ini = '20251201';
// $fim = '20260101'; // Corrigido para um mês válido


// Converter para o formato de data
$data_ini = DateTime::createFromFormat('Ymd', $ini);
$data_fim = DateTime::createFromFormat('Ymd', $fim);

$data_ini_atual = DateTime::createFromFormat('Ymd', $ini);
$data_fim_atual = DateTime::createFromFormat('Ymd', $fim);

// Verificar se as datas foram criadas corretamente
if (!$data_ini || !$data_fim) {
    die(json_encode(["error" => "Erro ao criar as datas. Verifique o formato informado."]));
}

// Subtrair um ano
$data_ini->modify('-1 year');
$data_fim->modify('-1 year');
$data_fim->modify('-1 day');

// Obter as novas datas no formato correto para o SQL
$ini_passado = $data_ini->format('Y-m-d');
$fim_passado = $data_fim->format('Y-m-d');

// Retornar para o ano atual e corrigir o $fim
$ini_atual = $data_ini_atual->format('Y-m-d'); 
$data_fim_atual->modify('-1 day'); // Modificar antes de formatar
$fim_atual = $data_fim_atual->format('Y-m-d');  // Retornar para o mês atual

// Definir os dias da semana
$semana = array(
    'Sun' => 'DOM', 
    'Mon' => 'SEG',
    'Tue' => 'TER',
    'Wed' => 'QUA',
    'Thu' => 'QUI',
    'Fri' => 'SEX',
    'Sat' => 'SAB'
);

// Preparar para coletar os dados
$dados = array();

// Consulta ao banco
$sql = $cnx->prepare("
    SELECT  
        COALESCE(a.data, b.data) AS data_referencia, 
        a.data AS ano_passado,
        a.vendaDia AS venda_ano_passado,
        a.rentDia AS rent_ano_passado,
        b.data AS ano_atual,
        b.vendaDia AS venda_ano_atual,
        b.rentDia AS rent_ano_atual
    FROM logman3__svdbi62 AS a
    LEFT JOIN logman3__svdbi62 AS b 
        ON a.grupoNume = b.grupoNume 
        AND DATE_FORMAT(a.data, '%m-%d') = DATE_FORMAT(b.data, '%m-%d') 
        AND YEAR(a.data) = YEAR(b.data) - 1
    WHERE a.grupoNume = 29 
    AND a.data BETWEEN ? AND ?

    UNION

    SELECT  
        COALESCE(a.data, b.data) AS data_referencia, 
        a.data AS ano_passado,
        a.vendaDia AS venda_ano_passado,
        a.rentDia AS rent_ano_passado,
        b.data AS ano_atual,
        b.vendaDia AS venda_ano_atual,
        b.rentDia AS rent_ano_atual
    FROM logman3__svdbi62 AS b
    LEFT JOIN logman3__svdbi62 AS a 
        ON a.grupoNume = b.grupoNume 
        AND DATE_FORMAT(a.data, '%m-%d') = DATE_FORMAT(b.data, '%m-%d') 
        AND YEAR(a.data) = YEAR(b.data) - 1
    WHERE b.grupoNume = 29 
    AND b.data BETWEEN ? AND ?
    ORDER BY data_referencia;
");

// Associar os parâmetros corretamente (4 strings)
$sql->bind_param("ssss", $ini_passado, $fim_passado, $ini_atual, $fim_atual);

// Executar a consulta
$sql->execute();
$result = $sql->get_result();


// Processar os resultados
while ($dad = $result->fetch_assoc()) {
    // Verificar o dia da semana
    $dia = null;
    if (!empty($dad['ano_atual'])) {
        // Garantir que a data seja válida
        $dia = date('D', strtotime($dad['ano_atual']));
        $dia = isset($semana[$dia]) ? $semana[$dia] : null;  // Atribuir o dia da semana se for válido
    }

    // Armazenar os dados
    $dados[] = array(
        "dataapassado" => !empty($dad['ano_passado']) ? date('d/m/Y', strtotime($dad['ano_passado'])) : null, // Garantir formato d/m/Y

        "dataatual" => $dad['ano_atual'] ? date('d/m/Y', strtotime($dad['ano_atual'])) : null,
        "datames" => date('Ymd', strtotime($ini)),
        "vendadiapassado" => $dad['venda_ano_passado'],
        "vendadiaatual" => $dad['venda_ano_atual'] ?? null,
        "dia" => $dia,
        "rentdiapassado" => $dad['rent_ano_passado'],
        "rentdiaatual" => $dad['rent_ano_atual']
    );
}


// Fechar conexão
$sql->close();
$cnx->close();

// Retornar os dados em JSON
echo json_encode($dados);
?>
