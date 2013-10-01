<?php
#spl_autoload_register(function($class) { return spl_autoload(str_replace('_', '/', $class));});
if (!function_exists ("__autoload")){
  function __autoload($class) {
    if(class_exists($class)) return;
    #$ext = strtolower(substr($class, strrpos($class, '.')));
    $class_path = dirname(__FILE__).DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';
    //check file exists
    if(!$classpath = realpath($class_path))
      trigger_error("Class file '$class_path' [$class] not found",E_USER_ERROR);
    //load if needed
    require_once $classpath;
    //check its loaded
    if(class_exists($class) || interface_exists($class)){
      return $class;
    }else{
      trigger_error("Failed to load class '$class'",E_USER_ERROR);
    }
  }
}