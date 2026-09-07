<?php
include '../assets/_db/db.php';
include '../assets/_php/SimpleXLS.php';
$abreArq = '../assets/arquivos/SAEBI51-DIARIO.xls';
    if (file_exists($abreArq)){
        if ($excel=SimpleXLS::parse($abreArq)){
            $posicao = 1;
            $datasrel = array();
            foreach($excel->rows() as $key => $coluna){
                if($key==0){
                    //echo "Arquivo Vazio!!!";
                }else{
                    for($i=0; $i<1; $i++){
                        array_push($datasrel, "$coluna[10]");
                    }
                }
            }
            $lista = array_unique($datasrel);
            $countlista = count($lista);
            // print_r($countlista);
            if ($countlista == 1){
                print_r($lista[0]);
                $data         = $lista[0];
                $dat          = explode('/',$data);
                $dataconsulta      = $dat[2].''.$dat[1].''.$dat[0];
                $cnx->query("DELETE FROM `logman3__saeoi51_history` WHERE dataeve = '$dataconsulta'");
                foreach($excel->rows() as $key => $coluna){
                    if($key==0){
                        //echo "Arquivo Vazio!!!";
                    }else{
                        for($i=0; $i<1; $i++){
                            $evento           = $coluna[1];
                            $codigoSepara     = explode('-',$coluna[3]);
                            $codigo           = $codigoSepara[0];
                            $subCodigo        = $codigoSepara[1];
                            $descricao        = $coluna[5];
                            $emb              = $coluna[6];
                            $emb1             = $coluna[7];
                            $emb9             = $coluna[8];
                            $valor            = $coluna[9];
                            // $dataEve          = date('Ydm',strtotime($coluna[10]));
                            $data             = $coluna[10];
                            $dat              = explode('/',$data);
                            $dataEve          = $dat[2].''.$dat[1].''.$dat[0];
                            $operacao         = $coluna[13];
                            $setorComp        = $coluna[21];
                            $grupo            = $coluna[17];
                            $setor            = $coluna[21];
    
                            $s = str_split($setorComp);
                            $setorNumero = "$s[0]$s[1]$s[2]";
                            $d = str_split($setorComp, 3);
                            $countArray = count($d) ;
                            $setorDescricao = "";
                            for($i=1; $i<$countArray; $i++){
                                // echo $d[$i]."<br>";
                                $setorDescricao .=$d[$i];
                            }
                            echo $setorDescricao."".$dataEve."<br>";                                
                            $query = "INSERT INTO logman3__saeoi51_history (`evento`, `codigo`, `sub`, `descricao`, `emb`, `emb1`, `emb9`, `valor`, `dataeve`, `operacao`, `setorNumero`, `setorDescricao`, `grupo`) 
                                                                    VALUES ('$evento',' $codigo','$subCodigo','$descricao','$emb','$emb1','$emb9','$valor','$dataEve','$operacao','$setorNumero','$setorDescricao','$grupo')";
                            // $cnx -> query($query);
                        }
                    }
                }
                // echo 101;
                $cnx->close();
            // }
            }else{
                echo "Arquivo com mais de ".$countlista." data(s) !!! <br> Selecione arquivo que somente 1 data !! ";
            }
        }else{
            echo SimpleXLS::parseError();
        }
    }else{
        echo 103;
    }
    ?>