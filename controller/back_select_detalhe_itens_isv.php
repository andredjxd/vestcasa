<?php
include '../assets/_db/db.php';
$cqu = isset($_POST['cqu']) ? trim($_POST['cqu']) : null;
$id = $_POST['id'];
// $cqu = 101;
// $id = 21;
function cortar_texto($texto, $limite = 12) {
    if (mb_strlen($texto, 'UTF-8') > $limite) {
        return mb_substr($texto, 0, $limite, 'UTF-8') . '...';
    }
    return $texto;
}

$dados = [];
if($cqu == 101){
    $stmt = $cnx->prepare("SELECT * FROM `logman3__parametros_relatorios` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $dad = $result->fetch_assoc();

    // Extrai parâmetros
    $ope01 = $dad['operador01'];   // Ex: ">="
    $dia01 = (int)$dad['dias01'];  // Ex: 7
    $ope02 = $dad['operador02'];   // Ex: "<="
    $dia02 = (int)$dad['dias02'];  // Ex: 30
    $opelogico = strtoupper(trim($dad['opelogico'])); // Ex: "IN" ou "NOT IN"
    $setores = $dad['setores'];    // Ex: "70,71,72"

    // Monta query dinâmica de forma segura
    $query = "
        SELECT * 
        FROM logman3__smgoi13 
        WHERE (nao_vende $ope01 $dia01 AND idade $ope02 $dia02)
        AND setor_numero $opelogico ($setores)
        ORDER BY total_estoque DESC 
    ";

    $res1 = $cnx->query($query);

    if ($res1) {
        $pos = 0;
        while ($row = $res1->fetch_assoc()) {
            $pos++;
            $dados[] = [
                "pos"           => $pos
                ,"codigo"       => $row['codigo']
                ,"sub_cod"      => $row['sub_cod']
                ,"descricao"    => $row['descricao']
                ,"emb"          => $row['emb']
                ,"dtutlent"     => $row['dt_ult_entr']
                ,"qtultent"     => $row['qtd_ult_entr']
                ,"cxa"          => $row['est_emb1']
                ,"und"          => $row['est_emb9']
                ,"ida"          => $row['idade']
                ,"isv"          => $row['nao_vende']
                ,"toe"          => $row['total_estoque']
                ,"vnd"          => $row['venda']
                ,"slj"          => cortar_texto($row['setor_numero']."-".$row['setor_loja'])
                ,"dta"          => $row['data']
                
            ];
            
        }
    } else {
        $dados = [
            "error" => "103",
            "message" => "Erro ao executar consulta: " . $cnx->error
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
}