<?php
include '../assets/_db/db.php';

$ini = $_POST['ini'];
$fim = $_POST['fim'];

// $ini = '20250301';
// $fim = '20250401'; // Corrigido para um mês válido


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

$ini_atual = $data_ini_atual->format('Y-m-d');
// Retornar para o ano atual e corrigir o $fim
$data_ini_atual->modify('-1 day'); // Modificar antes de formatar
$datamesant = $data_ini_atual->format('Y-m-d');
//  echo $datamesant; 
 
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


//echo $rDiaAnt."<br>";

$sql = $cnx->query("SELECT 
                        data, 
                        leitura06, 
                        nome1, 
                        leitura23, 
                        nome2,
                        leitura23 - leitura06 AS consudia, 
                        leitura06 - LAG(leitura23) OVER (ORDER BY data) AS noite
                    FROM logman3__agua_registro 
                    WHERE data BETWEEN '$ini_atual' AND '$fim_atual' 
                    ORDER BY `data` ASC");
while($dad = $sql->fetch_array()){
    if ($dad['data'] != NULL && $dad['noite'] == NULL){
        $SQLmesAnt = $cnx->query("SELECT data, leitura23 FROM logman3__agua_registro WHERE data = '$datamesant';");
        $SQLPrepare = $SQLmesAnt->fetch_array(MYSQLI_ASSOC);
        $rDiaAnt = $SQLPrepare['leitura23'] ?? 0; // Evita erro caso 'leitura23' seja NULL
        if($rDiaAnt == 0){
            $noite = $dad['noite'];
        }else{
            $noite = round((float)$dad['leitura06']-$rDiaAnt, 3);
        }
       
    }else{
        $noite = $dad['noite'];
    }
    $dia = date('D', strtotime($dad['data']));
    $dia = isset($semana[$dia]) ? $semana[$dia] : null;  // Atribuir o dia da semana se for válido
    $dados[]=array(
    "data"=>date('d/m/Y',strtotime($dad['data'])),
    "dia"=>$dia,
    "leitura06"=>$dad['leitura06'],
    "nome1"=>$dad['nome1'],
    "leitura23"=>$dad['leitura23'],
    "nome2"=>$dad['nome2'],
    "consumodia"=>$dad['consudia'],
    "consumonoite"=>$noite); 
}
echo json_encode($dados);