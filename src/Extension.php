<?php
namespace prototypr {
    /**
     * Extension class
     * @package prototypr
     */
    class Extension {
        /**
         * array of extensions keyed by class
         * self::$extensions[class]
         * @var array 
         */
        private static $extensions = [];
        
        /**
         * Checks is prototype is extending another
         * @param mixed $class
         * @param mixed $target
         * @return boolean
         */
        public static function extending($class,$target) {
            if(is_object($class)) {
                $class = get_class($class);
            }
            
            if(is_object($target)) {
                $target = get_class($target);
            }
            
            $target = strtolower($target);
            $class = strtolower($class);
            
            if(array_key_exists($class,self::$extensions) &&
               in_array($target,self::$extensions[$class])) {
                return true;
            }
            
            return false;
        }
        
        /**
         * Copies methods from one prototype to another
         * @param mixed $class
         * @param mixed $target
         */
        public static function addExtension($class,$target) {
            if(is_object($class)) {
                $class = get_class($class);
            }
            
            if(is_object($target)) {
                $target = get_class($target);
            }
            
            $target = strtolower($target);
            $class = strtolower($class);

            if(!array_key_exists($class,self::$extensions)) {
                self::$extensions[$class] = [];
            }
            
            self::$extensions[$class][] = (string)$target;
            
            Registry::addExtension($class, $target);
        }
        
        /**
         * Retrieve all extensions of a class
         * @param string $class
         * @return array
         */
        public static function getExtensions($class) {
            if(is_object($class)) {
                $class = get_class($class);
            }
            
            $class = strtolower($class);
            
            return self::$extensions[$class];
        }
        
        /**
         * Ensures oop-like extensions
         * @param string $class
         */
        public static function autoExtend($class) {
            if(array_key_exists($class,self::$extensions)) {
                foreach(self::$extensions[$class] as $extension) {
                    if($extension !== $class) {
                        Manager::extend($class,$extension);
                    }
                }
            }
            
            self::manageScope($class);
        }
        
        /**
         * Deliberately forgets scope
         * @param string $class
         */
        public static function manageScope($class) {
            if(Manager::scoped()) {
                $return = Manager::scope();
                $returnClass = strtolower(get_class($return));
                        
                if($returnClass === $class) {
                    Manager::clearScope();
                }
            }
        }
    }
}