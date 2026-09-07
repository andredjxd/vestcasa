<?php

//timezone

date_default_timezone_set('America/Araguaina');

// conexão com o banco de dados

define('BD_SERVIDOR', getenv('BD_SERVIDOR') ?: 'localhost');
define('BD_USUARIO', getenv('BD_USUARIO') ?: 'root');
define('BD_SENHA', getenv('BD_SENHA') ?: '');
define('BD_BANCO', getenv('BD_BANCO') ?: 'vestcasa');
define('BD_BANCOA', getenv('BD_BANCOA') ?: 'atacadao');
?>