<?php declare(strict_types = 1);

function custom_autoloader(string $class) : void {
    if (strpos($class, 'PHPUnit') !== false) {
        return ;
    } else {
        include 'src/' . str_replace('\\', '/', $class) . '.php';
    }
}

spl_autoload_register('custom_autoloader');

?>