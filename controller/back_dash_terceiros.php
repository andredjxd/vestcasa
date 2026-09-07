<?php
include '../assets/_db/db.php';

$semana = array(
    'Sun' => 'DOM', 
    'Mon' => 'SEG',
    'Tue' => 'TER',
    'Wed' => 'QUA',
    'Thu' => 'QUI',
    'Fri' => 'SEX',
    'Sat' => 'SAB'
);

$datehj = new DateTime("-0 days");
$datehoje = $datehj->format('Ymd');

$hconsul_terceiros = $cnx->query("SELECT data, COUNT(*) as quant FROM `logman3__terceiros` WHERE data = '$datehoje';");
$hqtd_ter = $hconsul_terceiros->fetch_array(MYSQLI_BOTH);

$dateonte = new DateTime("-1 days");
$dateontem = $dateonte->format('Ymd');

$oconsul_terceiros = $cnx->query("SELECT data, COUNT(*) as quant FROM `logman3__terceiros` WHERE data = '$dateontem';");
$oqtd_ter = $oconsul_terceiros->fetch_array(MYSQLI_BOTH);

for($i=0; $i<8; $i++){
    $dateanteontem = new DateTime("-$i days");
    $anteontem = $dateanteontem->format('Ymd');
    // echo $anteontem;
    $consul_terceiros = $cnx->query("SELECT data, COUNT(*) as quant FROM `logman3__terceiros` WHERE data = '$anteontem';");
    $qtd_ter = $consul_terceiros->fetch_array(MYSQLI_BOTH);
    $dia = $semana[date('D',strtotime($anteontem))];
    // echo $dia." - ".$qtd_ter[1]."<br>";
    // $dados[]=array("datater"    =>date('d/m/Y',strtotime($anteontem))
    // ,"qtdter"                   =>$qtd_ter[1]
    // ,"dia"                      =>$qtd_ter[1]
    // );
}
// echo $datanow." - ".$ontem." - ".$anteontem;

$dia = $semana[date('D',strtotime($anteontem))];
$dados[]=array("dataterhj"    =>date('d/m/Y',strtotime($datehoje))
,"dataterhjsem"               =>$semana[date('D',strtotime($datehoje))]
,"qtdterhj"                   =>$hqtd_ter[1]
,"dataontem"                  =>date('d/m/Y',strtotime($dateontem))
,"dataontemsem"               =>$semana[date('D',strtotime($dateontem))]
,"qtdterontem"                =>$oqtd_ter[1]
);

echo json_encode($dados);