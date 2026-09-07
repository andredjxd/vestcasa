<?php
include '../assets/_db/db.php';

header('Content-Type: application/json');

$data = $_POST['data'];

// $data = '2026-03-03';

$query = "
    SELECT 
        f.dt_fecha,
        SUM(f.saldocartao) totalpos,
        SUM(f.valor_deposito) totaldeposito,
        i.seguidores,
        i.reels,
        i.story,
        i.live
    FROM vest_relatorio_fechamento f
    LEFT JOIN vest_instagram i 
        ON i.data = f.dt_fecha
    WHERE f.dt_fecha = '$data'
    GROUP BY f.dt_fecha
";

$sql = $cnx->query($query);

if ($dad = $sql->fetch_assoc()) {

    $totalgeral = $dad['totalpos'] + $dad['totaldeposito'];

    $data       = (new DateTime($dad['dt_fecha']))->format('d/m/Y');
    $cartao     = number_format($dad['totalpos'], 2, ',', '.');
    $dinheiro   = number_format($dad['totaldeposito'], 2, ',', '.');
    $total      = number_format($totalgeral, 2, ',', '.');

    $seguidores = $dad['seguidores'] ?? 0;
    $reels      = $dad['reels'] ?? 0;
    $story      = $dad['story'] ?? 0;
    $live       = $dad['live'] ?? 0;

    // formatar seguidores em K
    if ($seguidores >= 1000) {
        $seguidores = number_format($seguidores / 1000, 1, ',', '.') . 'K';
    }

    $mensagem = "📅 $data\n\n";
    $mensagem .= "🏬 *Loja*: Mega Palmas\n\n";
    $mensagem .= "💳 *Cartão*: R$ $cartao\n";
    $mensagem .= "💵 *Dinheiro*: R$ $dinheiro\n";
    $mensagem .= "📊 *Faturamento total*: R$ $total\n\n";
    $mensagem .= "❌ *Cancelamento de venda*: (0)\n\n";
    $mensagem .= "📸 *Instagram*: ($seguidores)\n";
    $mensagem .= "🎬 *Reels*:: $reels\n";
    $mensagem .= "📖 *Story*: $story\n";
    $mensagem .= "📡 *Live*: $live **";

    echo json_encode([
        "error" => 0,
        "message" => $mensagem
    ], JSON_UNESCAPED_UNICODE);

} else {

    echo json_encode([
        "error" => 1,
        "message" => "Nenhum resultado encontrado"
    ], JSON_UNESCAPED_UNICODE);

}