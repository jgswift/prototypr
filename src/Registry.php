<?php
namespace prototypr {
    /**
     * Registry class
     * @package prototypr
     */
    class Registry {
        /**
         * multidimensional array of closures dilineated by class then method
         * self::$methods[class][method]
         * @var array
         */
        private static $methods = [];
        
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

            if(!Extension::extending($class, $target)) {
                Extension::addExtension($class, $target);
            } else {
                if(!array_key_exists($target,self::$methods)) {
                    self::$methods[$target] = [];
                }

                $functions = self::getPrototypes($target);

                if(array_key_exists($class,self::$methods)) {
                    self::$methods[$class] = array_merge(self::$methods[$class],$functions);
                } else {
                    self::$methods[$class] = $functions;
                }
            }
        }
        
        /**
         * shortcut method to add or remove prototypes
         * TODO: MOVE TO \prototypr namespace 5.6
         * @param mixed $object
         * @param mixed $name
         * @param callable $callback
         * @return mixed
         */
        static function prototype($object,$name,callable $callback=null) {
            if(is_callable($callback)) {
                return self::addPrototype($object,$name,$callback);
            }
            
            return self::getPrototype($object,$name);
        }
        
        /**
         * removes all prototypes from class
         * @param mixed $class
         */
        static function unregister($class) {
            if(is_object($class)) {
                $class = get_class($class);
            } elseif(!is_string($class)) {
                return;
            }
            
            if(!empty($class)) {
                $class = strtolower($class);
                if(array_key_exists($class,self::$methods)) {
                    self::$methods[$class] = [];
                }
            }
        }
        
        /**
         * when no name argument is provided prototypes() returns a list of all class prototypes 
         * otherwise this method just checks if class is already implementing named method
         * @param mixed $class
         * @param string $name
         * @return mixed
         */
        static function prototypes($class,$name = null) {
            if(is_null($name)) {
                if(is_object($class)) {
                    $class = get_class($class);
                }

                $class = strtolower($class);

                if(array_key_exists($class,self::$methods)) {
                    return self::$methods[$class];
                }

                return [];
            }

            $name = strtolower($name);

            if(!is_array($class)) {
                $class = [$class];
            }

            $results = self::getAllPrototypes($class);
            
            if(count($results) > 0) {
                if($name) {
                    if(array_key_exists($name,$results)) {
                        return $results[$name];
                    } else {
                        return false;
                    }
                }

                return $results;
            }

            return false;
        }
        
        /**
         * Helper method to aggregate prototypes of multiple classes
         * @param array $classes
         * @return array
         */
        protected static function getAllPrototypes(array $classes) {
            $results = [];
            
            foreach($classes as $c) {
                if(is_object($c)) {
                    $c = get_class($c);
                }
                if(is_string($c) || is_int($c)) {
                    $c = strtolower($c);
                    if(is_string($c)) {
                        if(array_key_exists($c,self::$methods)) {
                            $results = array_merge(self::getPrototypes($c),$results);
                            break;
                        }
                    }
                }
            }
            
            return $results;
        }

        /**
         * returns an array methods for a particular class
         * @param string $class
         * @return array
         */
        protected static function getPrototypes($class) {
            $class = strtolower($class);

            if(isset( self::$methods[$class])) {
                return self::$methods[$class];
            }
            
            return [];
        }

        /**
         * Retrieves a list of all classes above given class
         * @param mixed $class
         * @param array $parents
         * @return array
         */
        protected static function parents($class,$parents = []) {
            $parent = get_parent_class($class);

            if($parent) {
                $parents[] = $parent;
                $parents = self::parents($parent,$parents);
            }

            return $parents;
        }

        /**
         * returns a list of closures by class method
         * @param mixed $class
         * @param string $name
         * @return array
         */
        protected static function getPrototype($class,$name) {
            if(is_object($class)) {
                $class = get_class($class);
            }

            $class = strtolower($class);

            if(array_key_exists($class,self::$methods)&&
               array_key_exists($name,self::$methods[$class])) {
                return self::$methods[$class][$name];
            }
        }

        /**
         * clear prototypes for a specific class method
         * @param mixed $class
         * @param string $name
         */
        protected static function removePrototype($class,$name) {
            if(is_object($class)) {
                $class = get_class($class);
            }

            $class = strtolower($class);

            if(array_key_exists($class,self::$methods)&&
               array_key_exists($name,self::$methods[$class])) {
                unset(self::$methods[$class][$name]);
            }
        }

        /**
         * Add a prototype to a specific class method
         * @param mixed $class
         * @param string $name
         * @param callable $callback
         * @return mixed
         */
        protected static function addPrototype($class,$name,callable $callback) {
            if(!is_array($class)) {
                $classes = [$class];
            } else {
                $classes = $class;
            }

            $name = strtolower($name);
            $return = null;
            foreach($classes as $class) {
                self::addPrototypeHelper($class, $name, $callback);
            }

            return $return;
        }
        
        /**
         * Helper method to handle internal callback storage
         * and default extension behavior
         * @param string $class
         * @param string $name
         * @param callable $callback
         */
        private static function addPrototypeHelper($class,$name,callable $callback) {
            if(is_object($class)) {
                $class = get_class($class);
            }

            $class = strtolower($class);

            if(!array_key_exists($class,self::$methods)) {
                self::$methods[$class] = [];
            }

            if(!array_key_exists($name,self::$methods[$class])) {
                self::$methods[$class][$name] = [];
            } elseif(!is_array(self::$methods[$class][$name])) {
                self::$methods[$class][$name] = [self::$methods[$class][$name]];
            }

            self::$methods[$class][$name][] = $callback;

            Extension::autoExtend($class);
        }
    }
}