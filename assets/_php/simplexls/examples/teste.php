<?php

require_once __DIR__.'/../src/SimpleXLSX.php';

$xlsx = new SimpleXLSX( 'SAEOI51.xlsx' );
    try {
       $conn = new PDO( "mysql:host=localhost;dbname=atacadao", "root", "");
       $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e)
    {
        echo $sql . "<br>" . $e->getMessage();
    }
    $stmt = $conn->prepare( "INSERT INTO `logman2__saeoi51`(`filial`, `evento`, `descr_evento`, `cod`, `sub_cod`, `status`, `descricao`, `embalagem`, `emb1`, `emb9`, `vlr_total`, `dt_ult_ev`, `direito_dev`, `par`, `operacao`, `cod_grupo`, `cod_sub_grupo`, `cod_classe`, `grupo`, `sub_grupo`, `classe`, `comprador`, `setor`, `setor_balanco`) 
                            VALUES ('$rank','$country','$population','$date_of_estimate','$powp')");
    $stmt->bindParam( 1, $rank);
    $stmt->bindParam( 2, $country);
    $stmt->bindParam( 3, $population);
    $stmt->bindParam( 4, $date_of_estimate);
    $stmt->bindParam( 5, $powp);
    foreach ($xlsx->rows() as $fields)
    {
        $rank = $fields[0];
        $country = $fields[1];
        $population = $fields[2];
        $date_of_estimate = $fields[3];
        $powp = $fields[4];
        $stmt->execute();
    }

?>