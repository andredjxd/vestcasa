<?php
include '../assets/_db/db.php'; 
$rol = $_POST['rol'];
// $rol = 4168;

$ini = $_POST['ini'];
$fim = $_POST['fim'];

// $ini = '20250201';
// $fim = '20250301'; // Corrigido para um mês válido


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
$contador = 1; // Inicializa o contador

$dados = array();
$contador = 1; // Inicializa o contador

$consul_rol = $cnx->query("SELECT rol, data, descricao, codigo, codDescri, Qtd, valor FROM `logman3__almox` 
                            WHERE rol = '$rol' AND data BETWEEN '$ini_atual' AND '$fim_atual'");

while($qtd_rol = $consul_rol->fetch_array()){
    // Adiciona o contador e os dados no array
    $dados[] = array(
        "rol"           => $qtd_rol[0]
        ,"datamovi"     => $qtd_rol[1]
        ,"descricao"    => $qtd_rol[2]
        ,"codigo"       => $qtd_rol[3]
        ,"codescri"     => $qtd_rol[4]
        ,"qtd"          => $qtd_rol[5]
        ,"valor"        => $qtd_rol[6]
  
    );
    
    // Incrementa o contador
    $contador++;
}

echo json_encode($dados);
?>
