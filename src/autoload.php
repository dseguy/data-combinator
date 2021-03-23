<?php
function custom_autoloader($class) {
print $class;
  include 'src/' . str_replace('\\', '/', $class) . '.php';
}
 
spl_autoload_register('custom_autoloader');
 
?>