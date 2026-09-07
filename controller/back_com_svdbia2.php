<?php
include '../assets/_db/db.php';
$sql = $cnx->query("SELECT * FROM `logman3__svdbia2` ORDER BY `vlrverba` DESC");
while($dad = $sql->fetch_array()){
    // $sqlc = $cnx->query("SELECT COUNT(id_rel) AS itens FROM `logman3__validade_relatorio_item` WHERE id_rel = ".$dad['id']."");
    //$dads = $sqlc->fetch_array(MYSQLI_BOTH);
    //var_dump($dads);
    $cxa = $dad['mult1'] * $dad['mult2'];
    $vlrund = $dad['vlrverba'] / $dad['qtdverba'];
    $qtdmovim = $dad['qtdverba'] - $dad['qtdmovimento'];
    $qtdcxa = $qtdmovim / $cxa;
    $dados[]=array("codigo"=>$dad['codigo'],
    "sub"=>$dad['sub'],
    "data"=>date('d/m/Y',strtotime($dad['data'])),
    "descricao"=>$dad['descricao'],
    "mult1"=>$dad['mult1'],
    "mult2"=>$dad['mult2'],
    "emb1"=>$dad['emb1'],
    "emb2"=>$dad['emb2'],
    "dataini"=>date('d/m/Y',strtotime($dad['datainicial'])),
    "datafim"=>date('d/m/Y',strtotime($dad['datafinal'])),
    "qtdverba"=>$dad['qtdverba'],
    "vlrverba"=>$dad['vlrverba'],
    "vlrund"=>$vlrund,
    "qtdmovim"=>$dad['qtdmovimento'],
    "resmovim"=>$qtdmovim,
    "qtdcxa"=>$qtdcxa,
    "vlrmovim"=>$dad['vlrmovimento']);
}
echo json_encode($dados);