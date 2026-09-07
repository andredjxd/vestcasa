<?php
include '../assets/_db/db.php';

// $ini = '20260201';
// $fim = '20260301'; // Corrigido para um mês válido


$ini = $_POST['ini'];
$fim = $_POST['fim'];

// ================================
// 📅 Conversão de datas
// ================================
$data_ini = DateTime::createFromFormat('Ymd', $ini);
$data_ini->setTime(0, 0, 0);

$data_fim = DateTime::createFromFormat('Ymd', $fim);
$data_fim->setTime(23, 59, 59);
$data_fim->modify('-1 day'); // ajuste conforme regra de negócio

$dtIni = $data_ini->format('Y-m-d H:i:s');
$dtFim = $data_fim->format('Y-m-d H:i:s');


// ================================
// 📆 FUNCTION QUE CONTAS SEM OS DOMINGOS
// ================================
function diasNoMesSemDomingo(DateTime $data) {
    $ano  = $data->format('Y');
    $mes  = $data->format('m');
    $dias = (int) $data->format('t');

    $contador = 0;

    for ($dia = 1; $dia <= $dias; $dia++) {
        $dataAtual = new DateTime("$ano-$mes-$dia");
        
        // 'w' retorna:
        // 0 = Domingo
        // 1 = Segunda
        // ...
        // 6 = Sábado
        if ($dataAtual->format('w') != 0) {
            $contador++;
        }
    }

    return $contador;
}

// ================================
// 📆 Dias da semana
// ================================
$semana = [
    'Sun' => 'DOM',
    'Mon' => 'SEG',
    'Tue' => 'TER',
    'Wed' => 'QUA',
    'Thu' => 'QUI',
    'Fri' => 'SEX',
    'Sat' => 'SAB'
];

// ================================
// 📊 Consulta meta de vendas
// ================================
$dtIniMeta = $data_ini->format('Y-m-d');

$sql = $cnx->prepare("
    SELECT venda_meta 
    FROM vest_relatorio_vendas_meta 
    WHERE data = ?
");

$sql->bind_param("s", $dtIniMeta);
$sql->execute();
$sql->bind_result($venda_meta);
$sql->fetch();
$sql->close(); 

$venda_meta = $venda_meta ?? 0;
//echo($venda_meta."<br>");
$totalDiasMes = diasNoMesSemDomingo($data_ini);
//echo($totalDiasMes."<br>");


// ================================
// 📦 Array de retorno
// ================================
$dados = [];

// ================================
// 📊 Consulta principal (VENDAS + NF + DEV)
// ================================
// $sql = $cnx->prepare("
//     SELECT 
//         DATE(v.data_hora) AS data,
//         SUM(v.total) AS total,
//         COUNT(*) AS operacoes,
//         IFNULL(MAX(vrf.totalf), 0) AS totalf,
//         IFNULL(MAX(vrf.sangria), 0) AS sangria,

//         IFNULL(SUM(CASE 
//             WHEN n.natOp != 'devolução de mercadoria adquirida por não contribuinte'
//             THEN n.vOutro ELSE 0 END),0) AS juros,

//         IFNULL(SUM(CASE 
//             WHEN n.natOp != 'devolução de mercadoria adquirida por não contribuinte'
//             THEN n.vNF ELSE 0 END),0) AS totalnf,

//         COUNT(CASE 
//             WHEN n.natOp = 'devolução de mercadoria adquirida por não contribuinte'
//             THEN 1 END) AS devolqtd,

//         IFNULL(SUM(CASE 
//             WHEN n.natOp = 'devolução de mercadoria adquirida por não contribuinte'
//             THEN n.vNF ELSE 0 END),0) AS devolval

//     FROM vest_relatorio_vendas v

//     LEFT JOIN vest_relatorio_vendas_nota n 
//         ON n.nNF = v.num_nf
//         AND n.serie_nf = v.serie_nf

//     LEFT JOIN (
//         SELECT 
//             dt_fecha,
//             SUM(saldocartao) + SUM(valor_deposito) AS totalf,
//             SUM(valor_sangria) AS sangria
//         FROM vest_relatorio_fechamento
//         GROUP BY dt_fecha
//     ) vrf 
//         ON DATE(v.data_hora) = vrf.dt_fecha

//     WHERE v.status_nf = 100
//     AND v.data_hora >= ?
//     AND v.data_hora <= ?

//     GROUP BY DATE(v.data_hora)
//     ORDER BY data;
// ");
// $sql->bind_param("ss", $dtIni, $dtFim);

$sql = $cnx->prepare("
    SELECT 
        v.data_hora_date AS data,
        SUM(v.total) AS total,
        COUNT(*) AS operacoes,
        IFNULL(MAX(vrf.totalf), 0) AS totalf,
        IFNULL(MAX(vrf.sangria), 0) AS sangria,

        IFNULL(SUM(CASE 
            WHEN n.natOp != 'devolução de mercadoria adquirida por não contribuinte'
            THEN n.vOutro ELSE 0 END),0) AS juros,

        IFNULL(SUM(CASE 
            WHEN n.natOp != 'devolução de mercadoria adquirida por não contribuinte'
            THEN n.vNF ELSE 0 END),0) AS totalnf,

        COUNT(CASE 
            WHEN n.natOp = 'devolução de mercadoria adquirida por não contribuinte'
            THEN 1 END) AS devolqtd,

        IFNULL(SUM(CASE 
            WHEN n.natOp = 'devolução de mercadoria adquirida por não contribuinte'
            THEN n.vNF ELSE 0 END),0) AS devolval

    FROM (
        SELECT 
            *,
            DATE(data_hora) AS data_hora_date
        FROM vest_relatorio_vendas
        WHERE status_nf = 100
        AND data_hora BETWEEN ? AND ?
    ) v

    LEFT JOIN vest_relatorio_vendas_nota n 
        ON n.nNF = v.num_nf
        AND n.serie_nf = v.serie_nf

    LEFT JOIN (
        SELECT 
            dt_fecha,
            SUM(saldocartao) + SUM(valor_deposito) AS totalf,
            SUM(valor_sangria) AS sangria
        FROM vest_relatorio_fechamento
        WHERE dt_fecha BETWEEN ? AND ?
        GROUP BY dt_fecha
    ) vrf 
        ON v.data_hora_date = vrf.dt_fecha

    GROUP BY v.data_hora_date
    ORDER BY data;
");
$sql->bind_param("ssss", $dtIni, $dtFim, $dtIni, $dtFim);

$sql->execute();
$result = $sql->get_result();

// ================================
// 🔄 Processamento final
// ================================

$metaAtual = $venda_meta;
$totaDias = $totalDiasMes;
$totalVendido = 0;
$i = 1;
while ($dad = $result->fetch_assoc()) {

    $metaDiaria = ($venda_meta - $totalVendido) / $totaDias;
    $totalVendido = $totalVendido + (float)$dad['total'];
    $mediaDiaria = $totalVendido / $i;
    
    $restante = (float)$dad['total'] - $metaDiaria ;
    $data = $dad['data'];

    // Dia da semana
    $dia = null;
    if (!empty($data)) {
        $sigla = date('D', strtotime($data));
        $dia = $semana[$sigla] ?? null;
    }

    // Ticket médio
    $cmedio = ($dad['operacoes'] > 0)
        ? ($dad['total'] / $dad['operacoes'])
        : 0;

    $dados[] = [
        "datadia"    => $data,
        "semanadia"  => $dia,
        "operacoes"  => (int)$dad['operacoes'],
        "cmedio"     => round($cmedio, 2),
        "totaldia"   => (float)$dad['total'],
        "juros"      => (float)$dad['juros'],
        "totalnf"    => (float)$dad['totalnf'],
        "devolqtd"   => (int)$dad['devolqtd'],
        "devolval"   => (float)$dad['devolval'],
        "totalfcx"   => (float)$dad['totalf'],
        "totalsangr" => (float)$dad['sangria'],
        "metadiaria" => (float)$metaDiaria,
        "restante"   => (float)$restante,
        "dias"       => (float)$totalDiasMes,
        "dia"        => (float)$i,
        "totalvendi" => (float)$totalVendido,
    ];
    $i++;
    $totaDias--;
    
}

// ================================
// 🔒 Fechamento
// ================================
$sql->close();
$cnx->close();

// ================================
// 📤 Retorno JSON
// ================================
header('Content-Type: application/json');
echo json_encode($dados);

