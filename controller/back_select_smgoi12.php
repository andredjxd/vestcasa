<?php
include '../assets/_db/db.php';

// supondo que você queira as duas primeiras filiais
// $sql = $cnx->query("SELECT DISTINCT filial FROM logman3__smgoi12 ORDER BY filial ASC LIMIT 2;");
// $filiais = array_column($sql->fetch_all(MYSQLI_ASSOC), 'filial');

// if (count($filiais) < 2) {
//     exit('Menos de duas filiais na tabela.');
// }

$stmt = $cnx->prepare("
    
SELECT  
        a.filial,
        a.codigo,
        a.sub_codigo,
        a.descricao,
        a.emb,
        a.cm,
        a.nome_comprador,
        a.qtd_ped_comp,
        a.ped_filial,
        a.ped_nf_at,
        a.est_emb1          AS cxa,
        a.est_emb9          AS und,
        a.vnd30,
        a.cm * a.est_emb1 	AS valest,
        b.cm * b.est_emb1 	AS valestout,
        a.cm * a.vnd30 		AS valvnd,
        b.cm * b.vnd30 		AS valvndout,
        b.est_emb1          AS outracxa,
        b.vnd30             AS vnd30out,
        b.cm                AS cmout,
        a.est_emb1/(a.vnd30/30) AS aut,
        b.est_emb1/(b.vnd30/30) AS autout
    FROM  logman3__smgoi12  a
    LEFT  JOIN logman3__smgoi12 b
           ON  b.filial = 671
           AND b.codigo = a.codigo
    WHERE a.filial = 702;
");
// $stmt->bind_param('ss', $filiais[0], $filiais[1]);
$stmt->execute();
$result = $stmt->get_result();

$dados = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($dados);
?>
