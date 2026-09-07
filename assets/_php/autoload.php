<?php
include '../assets/_db/db.php';
include '../assets/_php/SimpleXLS.php';
include '../assets/_php/SimpleXLSX.php';
$basePath = dirname(__DIR__, 2); // volta duas pastas
require_once $basePath . '/assets/init.php';
require_once $basePath . '/assets/_php/libs/redbean/rb.php';


// Conexão com o banco usando constantes do init.php
R::setup(
    'mysql:host=' . BD_SERVIDOR . ';dbname=' . BD_BANCO,
    BD_USUARIO,
    BD_SENHA
);


// Autoload manual completo
spl_autoload_register(function ($class) {
    $autoload_map = [
        // PhpSpreadsheet
        'PhpOffice\\PhpSpreadsheet\\' => __DIR__ . '/../../assets/_php/libs/PhpSpreadsheet/src/PhpSpreadsheet/',
        // ZipStream
        'ZipStream\\' => __DIR__ . '/../../assets/_php/libs/zipstream/src/',
        // PSR SimpleCache
        'Psr\\SimpleCache\\' => __DIR__ . '/../../assets/_php/libs/psr/simple-cache/src/',
        // Pcre
        'Composer\\Pcre\\' => __DIR__ . '/../../assets/_php/libs/pcre-main/src/',
        // CKFinder
        'ckfinder\\' => __DIR__ . '/../../assets/_php/libs/ckfinder/',

        // PSR Logger
        'Psr\\Log\\' => __DIR__ . '/../../assets/_php/libs/psr/log/src/',
        // Symfony Config
        'Symfony\\Component\\Config\\' => __DIR__ . '/../../assets/_php/libs/symfony/Config/',
        
    ];

    foreach ($autoload_map as $prefix => $base_dir) {
        if (strpos($class, $prefix) === 0) {
            $relative_class = substr($class, strlen($prefix));
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }

    // Arquivos específicos
    if ($class === 'Psr\\SimpleCache\\CacheInterface') {
        require_once __DIR__ . '/../../assets/_php/libs/psr/simple-cache/src/CacheInterface.php';
    } elseif ($class === 'Pcre\\Main\\Preg') {
        require_once __DIR__ . '/../../assets/_php/libs/pcre-main/src/Preg.php';
    }
});

?>
