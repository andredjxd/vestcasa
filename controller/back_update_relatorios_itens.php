<?php
include '../assets/_db/db.php';
$id = $_POST['id'];
$cod = $_POST['cod'];
// $cod = 26780;
$sqlverf = $cnx->query("SELECT codigo FROM logman3__smgoi13 WHERE codigo = '".$cod."' ");
if ($sqlverf->num_rows == 0){
    $rentatu    = 0;
    $estoque    = 'ZERO';
    $vnd30      = 0;
    $sqlsrt = $cnx->query("SELECT codigo FROM logman3__srtbi03 WHERE codigo = '".$cod."' ");
    if ($sqlsrt->num_rows == 0){
        echo "SRT03 vazio ";
        $venda          = 'ZERO';
        $rentrea        = 0;
        $result         = 0;
        // echo $cod.' - '.$rentatu.' - '.$estoque.' - '.$vnd30.' - '.$venda.' - '.$rentrea.' - '.$result;
        $sqlup = "UPDATE `logman3__validade_relatorio_item` 
                    SET `rentatu`='$rentatu',`estoque`='$estoque',`vnd30ds`='$vnd30'
                    ,`vndreal`='$venda',`rentrea`='$rentrea',`resultado`='$result'
                    WHERE id_rel = ".$id." AND codigo = '$cod'";
        $exeup = $cnx->query($sqlup);
    }else{
        echo "SRT03 tem ";
        $sqlsrt2 = $cnx->query("SELECT emb1, emb9, rent, variacao FROM logman3__srtbi03 WHERE codigo = '".$cod."' ");
        while($dad = $sqlsrt2->fetch_array(MYSQLI_BOTH)){
            $venda = 'C:'.$dad['emb1'].' U:'.$dad['emb9'];
            $rentrea        = $dad['rent'];
            $result         = $dad['resultado'];
            // echo $cod.' - '.$rentatu.' - '.$estoque.' - '.$vnd30.' - '.$venda.' - '.$rentrea.' - '.$result;
            $sqlup = "UPDATE `logman3__validade_relatorio_item` 
                        SET `rentatu`='$rentatu',`estoque`='$estoque',`vnd30ds`='$vnd30'
                        ,`vndreal`='$venda',`rentrea`='$rentrea',`resultado`='$result'
                        WHERE id_rel = ".$id." AND codigo = '$cod'";
            $exeup = $cnx->query($sqlup);
        }
    }
    echo 101;

}else{
    $sql = $cnx->query("SELECT smg.codigo,smg.comprador,smg.rent AS arent,smg.est_emb1,smg.est_emb9,smg.qtd_venda_emb1,
                    srt.emb1,srt.emb9,srt.valor_venda,srt.rent AS rrent,srt.avaria,srt.ajuste,srt.resultado
                    FROM logman3__smgoi13 AS smg 
                    LEFT JOIN logman3__srtbi03 AS  srt ON  srt.codigo = smg.codigo
                    WHERE smg.codigo = '".$cod."'");
    while($dad = $sql->fetch_array(MYSQLI_BOTH)){
        if($dad['emb1'] == null and $dad['emb9'] == null){
            $venda = 'S/VND';
        }else{
            $venda = 'C:'.$dad['emb1'].' U:'.$dad['emb9'];
        }
        $codigo         = $dad['codigo'];
        $comprador      = $dad['comprador'];
        $rentatu        = $dad['arent'];
        $estoque        = 'C:'.$dad['est_emb1'].' U:'.$dad['est_emb9'];
        $vnd30          = $dad['qtd_venda_emb1'];
        $rentrea        = $dad['rrent'];
        $result         = $dad['resultado'];
        echo $codigo.' - '.$comprador.' - '.$rentatu.' - '.$estoque.' - '.$vnd30.' - '.$venda.' - '.$rentrea.' - '.$result;
        $sqlup = "UPDATE `logman3__validade_relatorio_item` 
                    SET `comprador`='$comprador',`rentatu`='$rentatu',`estoque`='$estoque',`vnd30ds`='$vnd30'
                    ,`vndreal`='$venda',`rentrea`='$rentrea',`resultado`='$result'
                    WHERE id_rel = ".$id." AND codigo = '$cod'";
        $exeup = $cnx->query($sqlup);
    }
    echo 101;
}
$data = date("Ymd");
$exedata = $cnx->query("UPDATE `logman3__validade_relatorio` SET `updatedata`='$data' WHERE id = '".$id."' ");
?>
