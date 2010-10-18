<?php
abstract class core_util_Singleton { 

    protected function __construct() { 
    } 

    final public static function getInstance() { 
        static $singletons = array(); 

        $class = get_called_class(); 

        if (! isset ($singletons[$class])) { 
            $singletons[$class] = new $class(); 
        } 

        return $singletons[$class]; 
    } 

    final private function __clone() { 
    } 
}
?>