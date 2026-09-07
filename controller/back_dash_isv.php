<?php
include '../assets/_db/db.php';
$cqu = isset($_POST['cqu']) ? trim($_POST['cqu']) : null;
// $cqu = 101;
/**
 * Função que retorna o total de estoque filtrado pelos parâmetros informados.
 *
 * @param mysqli $cnx Conexão MySQLi
 * @param string $ope01 Operador 1 (ex: >=)
 * @param int $dia01 Valor para nao_vende
 * @param string $ope02 Operador 2 (ex: >=)
 * @param int $dia02 Valor para idade
 * @param string $setores Lista de setores separados por vírgula
 * @return float Total do estoque (ou 0 se não encontrar)
 */
function calcularISV($cnx, $ope01, $dia01, $ope02, $dia02, $setores) {
    // ✅ Valida operadores
    $validOps = ['>', '>=', '<', '<=', '='];
    if (!in_array($ope01, $validOps)) $ope01 = '>=';
    if (!in_array($ope02, $validOps)) $ope02 = '>=';

    // ✅ Limpa a lista de setores
    $setores = preg_replace('/[^0-9,]/', '', $setores);
    $setoresLista = array_filter(explode(',', $setores));

    if (empty($setoresLista)) {
        return 0; // nenhum setor válido
    }

    // ✅ Monta placeholders dinâmicos (?, ?, ?, ...)
    $placeholders = implode(',', array_fill(0, count($setoresLista), '?'));

    // ✅ Monta SQL segura
    $sql = "
        SELECT SUM(total_estoque) AS total
        FROM logman3__smgoi13
        WHERE nao_vende $ope01 ?
          AND idade $ope02 ?
          AND setor_numero NOT IN ($placeholders)
    ";

    $stmt = $cnx->prepare($sql);
    if (!$stmt) {
        return 0;
    }

    // ✅ Define tipos e faz o bind dinâmico
    $tipos = str_repeat('i', 2 + count($setoresLista)); // todos inteiros
    $parametros = array_merge([$tipos, $dia01, $dia02], $setoresLista);

    $refs = [];
    foreach ($parametros as $key => $value) {
        $refs[$key] = &$parametros[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);

    // ✅ Executa e obtém resultado
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'] ?? 0;

    $stmt->close();
    return $total;
}
function calcularISVvnd($cnx, $ope01, $dia01, $ope02, $dia02, $setores) {
    // ✅ Valida operadores
    $validOps = ['>', '>=', '<', '<=', '='];
    if (!in_array($ope01, $validOps)) $ope01 = '>=';
    if (!in_array($ope02, $validOps)) $ope02 = '>=';

    // ✅ Limpa a lista de setores
    $setores = preg_replace('/[^0-9,]/', '', $setores);
    $setoresLista = array_filter(explode(',', $setores));

    if (empty($setoresLista)) {
        return 0; // nenhum setor válido
    }

    // ✅ Monta placeholders dinâmicos (?, ?, ?, ...)
    $placeholders = implode(',', array_fill(0, count($setoresLista), '?'));

    // ✅ Monta SQL segura
    $sql = "
        SELECT SUM(total_estoque) AS total
        FROM logman3__smgoi13
        WHERE nao_vende $ope01 ?
          AND idade $ope02 ?
          AND setor_numero NOT IN ($placeholders) AND venda = 'S'
    ";

    $stmt = $cnx->prepare($sql);
    if (!$stmt) {
        return 0;
    }

    // ✅ Define tipos e faz o bind dinâmico
    $tipos = str_repeat('i', 2 + count($setoresLista)); // todos inteiros
    $parametros = array_merge([$tipos, $dia01, $dia02], $setoresLista);

    $refs = [];
    foreach ($parametros as $key => $value) {
        $refs[$key] = &$parametros[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);

    // ✅ Executa e obtém resultado
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'] ?? 0;

    $stmt->close();
    return $total;
}
function calcularISVRelatorio($cnx, $ope01, $dia01, $ope02, $dia02, $opelo, $setores) {
    // ✅ Valida operadores
    $validOps = ['>', '>=', '<', '<=', '='];
    if (!in_array($ope01, $validOps)) $ope01 = '>=';
    if (!in_array($ope02, $validOps)) $ope02 = '>=';

    // ✅ Limpa a lista de setores
    $setores = preg_replace('/[^0-9,]/', '', $setores);
    $setoresLista = array_filter(explode(',', $setores));

    if (empty($setoresLista)) {
        return 0; // nenhum setor válido
    }

    // ✅ Monta placeholders dinâmicos (?, ?, ?, ...)
    $placeholders = implode(',', array_fill(0, count($setoresLista), '?'));

    // ✅ Monta SQL segura
    $sql = "
        SELECT SUM(total_estoque) AS total
        FROM logman3__smgoi13
        WHERE nao_vende $ope01 ?
          AND idade $ope02 ?
          AND setor_numero $opelo ($placeholders)
    ";

    $stmt = $cnx->prepare($sql);
    if (!$stmt) {
        return 0;
    }

    // ✅ Define tipos e faz o bind dinâmico
    $tipos = str_repeat('i', 2 + count($setoresLista)); // todos inteiros
    $parametros = array_merge([$tipos, $dia01, $dia02], $setoresLista);

    $refs = [];
    foreach ($parametros as $key => $value) {
        $refs[$key] = &$parametros[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);

    // ✅ Executa e obtém resultado
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'] ?? 0;

    $stmt->close();
    return $total;
}
function calcularISVvndRelatorio($cnx, $ope01, $dia01, $ope02, $dia02, $opelo, $setores) {
    // ✅ Valida operadores
    $validOps = ['>', '>=', '<', '<=', '='];
    if (!in_array($ope01, $validOps)) $ope01 = '>=';
    if (!in_array($ope02, $validOps)) $ope02 = '>=';

    // ✅ Limpa a lista de setores
    $setores = preg_replace('/[^0-9,]/', '', $setores);
    $setoresLista = array_filter(explode(',', $setores));

    if (empty($setoresLista)) {
        return 0; // nenhum setor válido
    }

    // ✅ Monta placeholders dinâmicos (?, ?, ?, ...)
    $placeholders = implode(',', array_fill(0, count($setoresLista), '?'));

    // ✅ Monta SQL segura
    $sql = "
        SELECT SUM(total_estoque) AS total
        FROM logman3__smgoi13
        WHERE nao_vende $ope01 ?
          AND idade $ope02 ?
          AND setor_numero $opelo ($placeholders) AND venda = 'S'
    ";

    $stmt = $cnx->prepare($sql);
    if (!$stmt) {
        return 0;
    }

    // ✅ Define tipos e faz o bind dinâmico
    $tipos = str_repeat('i', 2 + count($setoresLista)); // todos inteiros
    $parametros = array_merge([$tipos, $dia01, $dia02], $setoresLista);

    $refs = [];
    foreach ($parametros as $key => $value) {
        $refs[$key] = &$parametros[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);

    // ✅ Executa e obtém resultado
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'] ?? 0;

    $stmt->close();
    return $total;
}
// -------------------------------------------------------------------
// 🔁 Loop principal: percorre parâmetros e chama a função
// -------------------------------------------------------------------

$dados = [];
if($cqu == '100'){
    try {
        $res2 = $cnx->query("SELECT * FROM logman3__parametros");
        while ($dadosRes = $res2->fetch_assoc()) {
            $id      = trim($dadosRes['id']);
            $pos     = trim($dadosRes['pos']);
            $ope01   = trim($dadosRes['operador01']);
            $dia01   = trim($dadosRes['dias01']);
            $ope02   = trim($dadosRes['operador02']);
            $dia02   = trim($dadosRes['dias02']);
            $setores = trim($dadosRes['setores']);

            // 🔍 Consulta total Atual
            $totalEstoque       = calcularISV($cnx, $ope01, $dia01, $ope02, $dia02, $setores);
            $totalEstoqueVND    = calcularISVvnd($cnx, $ope01, $dia01, $ope02, $dia02, $setores);
            $totalEstoqueRest   = $totalEstoque - $totalEstoqueVND;

            // 🔍 Consulta total menos um dia
            $diaOntem = $dia01 - 1;
            $operadorFixo = "=";
            $operadorFixo2 = ">=";
            $totalEstoqueAmanha = calcularISV($cnx, $operadorFixo, $diaOntem, $operadorFixo2, $diaOntem, $setores);
            $totalEstoqueVNDAma = calcularISVvnd($cnx, $operadorFixo, $diaOntem, $operadorFixo2, $diaOntem, $setores);
            $totalEstoqueRestAm = $totalEstoqueAmanha - $totalEstoqueVNDAma;

            
            // echo "Restante: " . $totalEstoqueRest . " Restante dia anterior: " . $totalISVAmanha . "<br>";

            // echo "Dia hoje: $dia01<br>";
            // echo "Dia Ontem: $diaOntem<br>";
            // echo "Setores: $setores<br>";
            // echo "Op1: $ope01, Op2: $ope02<br>";
            $totalAmanha = $totalEstoqueRest + $totalEstoqueRestAm;

            // 💬 Exibe resultado
            //echo "ID $id → Total de estoque filtrado: $totalEstoque<br>";

            // 🧩 Adiciona resultado em formato legível
            $dados[] = [
                'id' => $id
                ,'dia' => $dia01
                ,'pos' => $pos
                ,'total_estoque' => $totalEstoque
                ,'total_estoqueVND' => $totalEstoqueVND
                ,'total_estoqueRest' => $totalEstoqueRest
                ,'total_isv_amanha' => $totalAmanha
            ];
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Erro ao acessar tabela logman3__parametros: " . $e->getMessage());
        $dados = [
            "error" => "101",
            "message" => "Tabela 'logman3__parametros' não encontrada no DB."
        ];
    }

    if (empty($dados)) {
        $dados = [
            "error" => "102",
            "message" => "Não há indicador de ISV cadastrado. É necessário realizar o cadastro!"
        ];
        header('Content-Type: application/json');
        echo json_encode($dados);
        
    } else if (!empty($dados) && !isset($dados['error'])){
        // Ordena pelo campo "pos"
        usort($dados, function($a, $b) {
            return (int)$a['pos'] <=> (int)$b['pos'];
        });

        // Exibe o resultado ordenado
        header('Content-Type: application/json');
        echo json_encode($dados);

    }else{
        header('Content-Type: application/json');
        echo json_encode($dados);
    }
}elseif($cqu == '101'){
    try {
        $res2 = $cnx->query("SELECT * FROM logman3__parametros_relatorios");
        while ($dadosRes = $res2->fetch_assoc()) {
            $id      = trim($dadosRes['id']);
            $pos     = trim($dadosRes['pos']);
            $nome    = trim($dadosRes['nome']);
            $setor   = trim($dadosRes['setor_nome']);
            $ope01   = trim($dadosRes['operador01']);
            $dia01   = trim($dadosRes['dias01']);
            $ope02   = trim($dadosRes['operador02']);
            $dia02   = trim($dadosRes['dias02']);
            $opelo   = trim($dadosRes['opelogico']);
            $setores = trim($dadosRes['setores']);

            // 🔍 Consulta total Atual
            $TotalISVRelatorio      = calcularISVRelatorio($cnx, $ope01, $dia01, $ope02, $dia02, $opelo, $setores);
            $TotalISVVNDRelatorio   = calcularISVvndRelatorio($cnx, $ope01, $dia01, $ope02, $dia02, $opelo, $setores);
            $totalEstoqueRest   = $TotalISVRelatorio - $TotalISVVNDRelatorio;

            // 🔍 Consulta total menos um dia
            $diaOntem = $dia01 - 1;
            $operadorFixo = "=";
            $operadorFixo2 = ">=";
            $totalEstoqueAmanha = calcularISVRelatorio($cnx, $operadorFixo, $diaOntem, $operadorFixo2, $diaOntem, $opelo, $setores);
            $totalEstoqueVNDAma = calcularISVvndRelatorio($cnx, $operadorFixo, $diaOntem, $operadorFixo2, $diaOntem, $opelo, $setores);

            $totalEstoqueRestAm = $totalEstoqueAmanha - $totalEstoqueVNDAma;
            // 🔍 Somatoria total Atual e total menos um dia
            $totalAmanha = $totalEstoqueRest + $totalEstoqueRestAm;

            
            // echo "Restante: " . $totalEstoqueRest . " Restante dia anterior: " . $totalISVAmanha . "<br>";

            // echo "Dia hoje: $dia01<br>";
            // echo "Dia Ontem: $diaOntem<br>";
            // echo "Setores: $setores<br>";
            // echo "Op1: $ope01, Op2: $ope02<br>";
            // echo "Total Restante: $totalEstoqueRest, <br>Total Restante Amanhã: $totalEstoqueRestAm<br>";
            // $totalAmanha = $totalEstoqueRest + $totalEstoqueRestAm;

            // // 💬 Exibe resultado
            // echo "ID $id → Total de estoque filtrado: $totalAmanha<br>";
            // echo "+===============================================================================+<br>";

            // 🧩 Adiciona resultado em formato legível
            $dados[] = [
                'id' => $id
                ,'pos' => $pos
                ,'nome' => " - ".$nome
                ,'setor' => $setor
                ,'ope01' => $ope01
                ,'dia01' => $dia01
                ,'totalisvrel' => $TotalISVRelatorio
                ,'totalisvvndrel' => $TotalISVVNDRelatorio
                ,'totalisvrest' => $totalEstoqueRest
                ,'totoalisvamanha' => $totalAmanha
            ];
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Erro ao acessar tabela logman3__parametros_relatorios: " . $e->getMessage());
        $dados = [
            "error" => "101",
            "message" => "Tabela 'logman3__parametros_relatorios' não encontrada no DB."
        ];
    }

    if (empty($dados)) {
        $dados = [
            "error" => "102",
            "message" => "Não há Relatorio de ISV cadastrado. É necessário realizar o cadastro!"
        ];
        header('Content-Type: application/json');
        echo json_encode($dados);
        
    } else if (!empty($dados) && !isset($dados['error'])){
        // Ordena pelo campo "pos"
        usort($dados, function($a, $b) {
            return (int)$a['pos'] <=> (int)$b['pos'];
        });

        // Exibe o resultado ordenado
        header('Content-Type: application/json');
        echo json_encode($dados);

    }else{
        header('Content-Type: application/json');
        echo json_encode($dados);
    }
}

?>
