<?php
// include '_db/db.php';
include '../assets/_db/db.php';

$filename = '../assets/arquivos/rel_subsidio_gerente.txt';
$txt_file = fopen($filename,'r');
function pega($ii,$ff,$a){
  for ($i=$ii; $i < $ff; $i++) {
    //echo $a[$i]."<br>";
    $codigo[] = "$a[$i]";
    $imp = implode($codigo);
    //print_r ($imp);
  }
  return $imp;
}
function trataValor($va){
  $a = str_replace(".","", $va);
  $b = str_replace(",",".", $a);
  return $b;
}
function datadb($d){
  $n = explode('/',$d);
  $r = "$n[2]$n[1]$n[0]";
  return $r;
}
$l = 0;
//$limpa = $cnx -> query("DELETE FROM logman3__produtos"); // 8-24  max:210
while ($line = fgets($txt_file)) {
  $a = str_split($line);
  if($l == 0 ){
    $data     = datadb(trim(pega(186,197,$a)));
    $codfil   = trim(pega(8,11,$a));
  }elseif($l >= 5){
    $comprador  = trim(pega(0,5,$a)); // 5
    $cod        = trim(pega(7,13,$a)); // 6
    $desc       = trim(pega(13,50,$a)); // 37
    $emb        = trim(pega(50,71,$a)); // 21
    $cxa        = trim(pega(71,76,$a)); // 5
    $und        = trim(pega(77,80,$a)); // 3
    $pro        = trim(pega(81,113,$a)); // 32
    $subVlr     = trataValor(trim(pega(114,121,$a)));
    $subUnd     = trim(pega(122,130,$a));
    $datVal     = datadb(trim(pega(132,140,$a))); // 8
    $totVal     = trataValor(trim(pega(141,150,$a))); // 9
    $observ     = trim(pega(161,210,$a)); // 49
    //var_dump($cod);
    //echo "$data - $codfil - $comprador - $cod - $desc - $emb - $cxa - $und - $pro - $subVlr - $subUnd - $datVal - $totVal - $observ<br>";
    $sqlsub = $cnx->query("SELECT dataadd,codigo FROM `logman3__subsidio` WHERE dataadd = '$data' AND codigo = '$cod'");
    $result = $sqlsub->fetch_array(MYSQLI_ASSOC);
    if(!$result){
      $query = "INSERT INTO `logman3__subsidio`( `dataadd`, `filial`,`comprador`, `codigo`, `descricao`, `emb`, `cxa`, `und`, `promocao`, `vlr_und`, `qtd_und`, `validade`, `vlr_total`, `obs`) 
            VALUES ('$data', '$codfil', '$comprador', '$cod', '$desc', '$emb', '$cxa', '$und', '$pro', '$subVlr', '$subUnd', '$datVal', '$totVal', '$observ')";
      $cnx -> query($query);
    }
    //echo "<br>";
  }
  $l++;
}
fclose($txt_file);

?>