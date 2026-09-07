<?php

require '../assets/_php/autoload.php'; // Carrega autoload do Composer

// Criar novo produto
$produto = R::dispense('produto');
$produto->nome = 'Notebook Dell';
$produto->preco = 3500.00;
$produto->estoque = 10;
R::store($produto);

// Buscar todos os produtos
$produtos = R::findAll('produto');

// Exibir
foreach ($produtos as $p) {
    echo $p->id . ' - ' . $p->nome . ' - R$ ' . number_format($p->preco, 2, ',', '.') . '<br>';
}

// Buscar com filtro
$notebook = R::findOne('produto', 'nome = ?', ['Notebook Dell']);
if ($notebook) {
    echo "Produto encontrado: {$notebook->nome}";
}

// Atualizar
$notebook->estoque = 8;
R::store($notebook);

// Deletar
// R::trash($notebook);
?>
