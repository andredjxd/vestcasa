<?php
include '../assets/_db/db.php';

$consul_venda = $cnx->query("SELECT data FROM logman3__smgoi13 ORDER BY data DESC LIMIT 1");
$dt_smg13 = $consul_venda->fetch_array(MYSQLI_BOTH);

$consulvnd = $cnx->query("SELECT DISTINCT dtvnd FROM logman3__smgoi13 WHERE dtvnd IS NOT NULL ORDER BY dtvnd DESC;")
$dt_vnd = $consulvnd->fetch_array(MYSQLI_BOTH);

$isvConsul = $cnx->query("SELECT SUM(total_estoque) FROM logman3__smgoi13 WHERE nao_vende >= 7 AND idade >= 7 AND setor_numero NOT IN ('70','71','72','73','100') ORDER BY total_estoque DESC");
$isv7 = $isvConsul->fetch_array(MYSQLI_BOTH);
$isvConsulvend = $cnx->query("SELECT SUM(total_estoque) FROM logman3__smgoi13 WHERE nao_vende >= 7 AND idade >= 7 AND venda = 'S' AND setor_numero NOT IN ('70','71','72','73','100') ORDER BY total_estoque DESC");
$isv7vend = $isvConsulvend->fetch_array(MYSQLI_BOTH);
$liqISV7 = $isv7[0] - $isv7vend[0];

$consul_srm28 = $cnx->query("SELECT dt_referencia FROM logman3__srmbi28 ORDER BY dt_referencia DESC LIMIT 1");
$dt_srm28 = $consul_srm28->fetch_array(MYSQLI_BOTH);
$totalentrada = 0;
$res1 = $cnx->query("SELECT srm.codigo, smg.nao_vende, smg.venda, srm.tot1 * smg.custo_medio AS estoque
                    FROM logman3__srmbi28 AS srm
                    LEFT JOIN logman3__smgoi13 as smg ON smg.codigo = srm.codigo
                    WHERE smg.nao_vende >= 7 AND smg.idade >= 7");
while($dadosRes = $res1->fetch_array()){
    $total          = $dadosRes['estoque'];
    $totalentrada   = $totalentrada + $total;
}
$totalentradaVND = 0;
$res2 = $cnx->query("SELECT srm.codigo, smg.nao_vende, smg.venda, srm.tot1 * smg.custo_medio AS estoque
                    FROM logman3__srmbi28 AS srm
                    LEFT JOIN logman3__smgoi13 as smg ON smg.codigo = srm.codigo
                    WHERE smg.nao_vende >= 7 AND smg.idade >= 7 AND smg.venda = 'S'");
while($dadosRes = $res2->fetch_array()){
    $total              = $dadosRes['estoque'];
    $totalentradaVND    = $totalentradaVND + $total;
}
$totalentrada6 = 0;
$res3 = $cnx->query("SELECT srm.codigo, smg.nao_vende, smg.venda, srm.tot1 * smg.custo_medio AS estoque
                    FROM logman3__srmbi28 AS srm
                    LEFT JOIN logman3__smgoi13 as smg ON smg.codigo = srm.codigo
                    WHERE smg.nao_vende = 6 AND smg.idade >= 6");
while($dadosRes = $res3->fetch_array()){
    $total          = $dadosRes['estoque'];
    $totalentrada6   = $totalentrada6 + $total;
}
$totalentrada6VND = 0;
$res4 = $cnx->query("SELECT srm.codigo, smg.nao_vende, smg.venda, srm.tot1 * smg.custo_medio AS estoque
                    FROM logman3__srmbi28 AS srm
                    LEFT JOIN logman3__smgoi13 as smg ON smg.codigo = srm.codigo
                    WHERE smg.nao_vende = 6 AND smg.idade >= 6 AND smg.venda = 'S'");
while($dadosRes = $res4->fetch_array()){
    $total               = $dadosRes['estoque'];
    $totalentrada6VND    = $totalentrada6VND + $total;
}
$isvConsul6 = $cnx->query("SELECT SUM(total_estoque) FROM logman3__smgoi13 WHERE nao_vende = 6 AND idade >= 6 AND setor_numero NOT IN ('70','71','72','73','100') ORDER BY total_estoque DESC");
$isv6 = $isvConsul6->fetch_array(MYSQLI_BOTH);
$isv6Consulvend = $cnx->query("SELECT SUM(total_estoque) FROM logman3__smgoi13 WHERE nao_vende = 6 AND idade >= 6 AND venda = 'S' AND setor_numero NOT IN ('70','71','72','73','100') ORDER BY total_estoque DESC");
$isv6vend = $isv6Consulvend->fetch_array(MYSQLI_BOTH);
$liqISV6 = $isv6[0] - $isv6vend[0];

$isvConsul5 = $cnx->query("SELECT SUM(total_estoque) FROM logman3__smgoi13 WHERE nao_vende = 5 AND idade >= 5 AND setor_numero NOT IN ('70','71','72','73','100') ORDER BY total_estoque DESC");
$isv5 = $isvConsul5->fetch_array(MYSQLI_BOTH);
$isv5Consulvend = $cnx->query("SELECT SUM(total_estoque) FROM logman3__smgoi13 WHERE nao_vende = 5 AND idade >= 5 AND venda = 'S' AND setor_numero NOT IN ('70','71','72','73','100') ORDER BY total_estoque DESC");
$isv5vend = $isv5Consulvend->fetch_array(MYSQLI_BOTH);
$liqISV5 = $isv5[0] - $isv5vend[0];

$datadehoje = new DateTime();
$dataummes= new DateTime('+1 month');
// echo $datadehoje->format('Y-m-d')."<br>";
// echo $dataummes->format('Y-m-01')."<br>";
$dataultimodia= new DateTime('-1 day');
// echo $dataultimodia->format($dataummes->format('Y-m-01')."<br>")."<br>";
$totalvendIsv = $isv7vend[0]+$isv6vend[0]+$isv5vend[0];
$totalamanhaISV = $liqISV7+$liqISV6;
$totalvendEntrada = $totalentrada - $totalentradaVND;
$totalvendEntrada6 = $totalentrada6 - $totalentrada6VND;

$dados[]= array(
"datasmg" => isset($dt_smg13[0]) ? date('d/m/Y', strtotime($dt_smg13[0])) : "BD Vazio"
,"datavnd" => isset($dt_vnd[0]) ? date('d/m/Y', strtotime($dt_vnd[0])) : "Relatorio Vazio"
,"isv7dias"             =>$isv7[0]
,"isv7diasvend"         =>$isv7vend[0]
,"isv7diasrest"         =>$liqISV7
,"datasrm"              =>$dt_srm28[0]
,"isv7diasentra"        =>$totalentrada
,"isv7diasentravnd"     =>$totalentradaVND
,"isv6dias"             =>$isv6[0]
,"isv6diasvend"         =>$isv6vend[0]
,"isv6diasrest"         =>$liqISV6
,"isv6diasentra"        =>$totalentrada6
,"isv6diasentravnd"     =>$totalentrada6VND
,"isv5dias"             =>$isv5[0]
,"isv5diasvend"         =>$isv5vend[0]
,"isv5diasrest"         =>$liqISV5
,"isvamanha"            =>$totalamanhaISV
,"isvtotalvend"         =>$totalvendIsv
,"isvtotalvendEntra"    =>$totalvendEntrada
,"isvtotalvendEntra6"   =>$totalvendEntrada6
);


echo json_encode($dados);