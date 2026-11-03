
<?php

spl_autoload_register(function ($class) {
    $folders = [
        __DIR__ . '/../Core/',
        __DIR__ . '/../Models/',
        __DIR__ . '/../Controllers/',
    ];

    foreach ($folders as $folder) {
        $file = $folder . $class . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
