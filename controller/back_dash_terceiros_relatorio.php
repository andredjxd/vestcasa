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
$dados = array();
// for($i=0; $i<8; $i++){
//     $dateanteontem = new DateTime("-$i days");
//     $anteontem = $dateanteontem->format('Ymd');
    // echo $anteontem;
$consul_terceiros = $cnx->query("SELECT ter.data,
                                COUNT(CASE WHEN ter.tipo = 'ZELADORIA' THEN ter.id END)'ZELADORIA',
                                COUNT(CASE WHEN ter.tipo = 'VISITANTE' THEN ter.id END)'VISITANTE',
                                COUNT(CASE WHEN ter.tipo = 'VIGILANTE' THEN ter.id END)'VIGILANTE',
                                COUNT(CASE WHEN ter.tipo = 'RECICLAGEM' THEN ter.id END)'RECICLAGEM',
                                COUNT(CASE WHEN ter.tipo = 'PROMOTOR' THEN ter.id END)'PROMOTOR',
                                COUNT(CASE WHEN ter.tipo = 'PORTEIRO' THEN ter.id END)'PORTEIRO',
                                COUNT(CASE WHEN ter.tipo = 'MANUTENCAO' THEN ter.id END)'MANUTENCAO',
                                COUNT(CASE WHEN ter.tipo = 'INVENTARIANTE' THEN ter.id END)'INVENTARIANTE'
                                FROM `logman3__terceiros` as ter
                                GROUP BY ter.data ORDER by data DESC;");
// $qtd_ter = $consul_terceiros->fetch_array(MYSQLI_BOTH);
while($qtd_ter = $consul_terceiros->fetch_array()){
    $dia = $semana[date('D',strtotime($qtd_ter[0]))];
    // echo $anteontem." - ".$dia." - ".$qtd_ter[1]." - ".$qtd_ter[2]."<br>";
    // // var_dump($qtd_ter);
    $dado[]=array("datater"     =>date('d/m/Y',strtotime($qtd_ter[0]))
    ,"dia"                      =>$dia
    ,"zeladoria"                =>$qtd_ter[1]
    ,"visitante"                =>$qtd_ter[2]
    ,"vigilante"                =>$qtd_ter[3]
    ,"reciclagem"               =>$qtd_ter[4]
    ,"promotor"                 =>$qtd_ter[5]
    ,"porteiro"                 =>$qtd_ter[6]
    ,"manutencao"               =>$qtd_ter[7]
    ,"inventariante"            =>$qtd_ter[8]
    
    );
}
// }
// echo $datanow." - ".$ontem." - ".$anteontem;

// $dia = $semana[date('D',strtotime($qtd_ter[0]))];
// $dados[]=array("data"    =>date('d/m/Y',strtotime($qtd_ter[0]))
// ,"dataterhjsem"               =>$semana[date('D',strtotime($dia))]
// // ,"qtdterhj"                   =>$hqtd_ter[1]
// // ,"dataontem"                  =>date('d/m/Y',strtotime($dateontem))
// // ,"dataontemsem"               =>$semana[date('D',strtotime($dateontem))]
// // ,"qtdterontem"                =>$oqtd_ter[1]
// );

echo json_encode($dado);