<?php
spl_autoload_register(function ($class) {
    $baseDir = __DIR__;

    $relativeClass = str_replace('\\', '/', $class);

    $file = $baseDir . '/'.$relativeClass . '.php';
    if (file_exists($file)) {
        require $file;
    }
});