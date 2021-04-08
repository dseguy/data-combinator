<?php declare(strict_types = 1);

function custom_autoloader(string $class): void {
    if (strpos($class, 'PHPUnit') !== false) {
        return ;
    } else {
        $file = 'src/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            include $file;
        }
    }
}

spl_autoload_register('custom_autoloader');

?>