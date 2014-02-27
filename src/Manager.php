<?php
namespace prototypr {
    use kindentfy;
    /**
     * Manager class
     * @package prototypr
     */
    class Manager {
        /**
         * Array of objects keyed by unique id
         * @var array
         */
        private static $uids = [];
        
        /**
         * run-time only, transient
         * @var mixed
         */
        private static $scope;
        
        /**
         * Retrieve a unique identifier for object instance
         * @param mixed $object
         * @return string
         */
        private static function identify($object) {
            if(($uid = array_search($object,self::$uids))) {
                return $uid;
            }

            $uid = kindentfy\Identifier::identify($object);
            self::$uids[$uid] = $object;
            return $uid;
        }
        
        /**
         * if argument conditions are met, adds a prototype method from global scope
         * when global scope is set, this function will return that scope
         * this allows the chaining from initially static method calll
         * @param string $class
         * @param string $name
         * @param array $arguments
         * @return mixed
         */
        static function callStatic($class,$name,array $arguments=[]) {
            if(is_array($arguments) && 
               count($arguments) > 0 && 
               isset($arguments[0]) && 
               is_callable($arguments[0])) {
                $scope = Registry::prototype($class,$name,$arguments[0]);
                return (!is_null($scope)) ? $scope : null;
            }
        }
        
        /**
         * Adds prototype method locally if argument matches signature
         * otherwise calls existing prototype methods
         * @param mixed $object
         * @param string $name
         * @param array $arguments
         * @return mixed
         * @throws prototypr\Exception
         */
        static function call($object,$name,array $arguments=[]) {
            $class = get_class($object);
            $uid = self::identify($object);

            // DOES PROTOTYPE EXIST, IF SO INVOKE IT
            if(($result = Registry::prototypes([$class,$uid],$name)) !== false) {
                if(count($arguments) === 1 &&
                   $arguments[0] instanceof \Closure) {
                    self::callStatic(get_class($object),$name,$arguments);
                    self::clearScope();
                    return $object;
                }

                // 99% WILL LAND HERE
                return self::invoke([$class,$uid],$name,$arguments,$object);
            }

            self::newScope( $object );
            // ARE WE JUST ADDING A NEW PROTOTYPE?

            if(is_array($arguments) && 
               count($arguments) > 0 && 
               isset($arguments[0]) && 
               $arguments[0] instanceof \Closure) {
               $newproto = Registry::prototype([$object,$uid],$name,$arguments[0]);
            }

            if(!isset($newproto) && 
               !is_bool( $methods = Registry::prototypes($object,$name))) {
                if(isset($methods) && 
                   count($methods) > 0) {
                    foreach($methods as $method) {
                        if($method instanceof \Closure) {
                            $method = $method->bindTo($object);
                        }

                        if(!is_callable($method)) {
                            throw new Exception(print_r($method,true).' is an invalid callback');
                        }

                        if(!is_null($result = call_user_func_array($method,$arguments))) {
                            return $result;
                        }
                    }

                    return;
                }
            }

            if(!isset($newproto)) {
                throw new Exception('Method ("'.$name.'") not found ("'.get_class($object).'")');
            }

            self::clearScope();
            return $object;
        }

        /**
         * Sets transient global scope
         * @param mixed $object
         */
        private static function newScope($object) {
            self::$scope = $object;
        }

        /**
         * Clears transient global scope
         */
        public static function clearScope() {
            self::$scope = null;
        }

        /**
         * Checks if global scope exists
         * or if global scope is provided object
         * @param mixed $object
         * @return boolean
         */
        public static function scoped($object=null) {
            if(is_null($object)) {
                return !empty(self::$scope);
            }
            return (self::$scope === $object);
        }
        
        /**
         * Retrieve current global scope
         * @return mixed
         */
        public static function scope() {
            return self::$scope;
        }
        
        static function extending($class,$name) {
            return Registry::extending($class,$name);
        }

        /**
         * Extend prototype from another already defined prototype
         * @param mixed $class
         * @param mixed $name
         * @param boolean $parents
         * @return mixed
         */
        static function extend($class,$name,$parents = false) {
            if(is_array($class)) {
                foreach($class as $c) {
                    self::extend($c,$name,$parents);
                }
                return;
            }

            if(is_string($class)) {
                if(array_key_exists($class,self::$uids)) {
                    $class = self::$uids[$class];
                }
            }

            if(is_object($class)) {
                $obj = $class;
                $class = get_class($obj);
            }

            if(empty($class)) {
                return;
            }
            
            if(is_object($name)) {
                $name = get_class($name);
            }

            Registry::addExtension($class,$name);
        }

        /**
         * Performs call on prototype
         * @param mixed $class
         * @param string $name
         * @param array $arguments
         * @param mixed $scope
         * @return mixed
         */
        protected static function invoke($class,$name,$arguments,$scope) {
            if(!is_array($class)) {
                $classes = [$class];
            } else {
                $classes = $class;
            }

            foreach($classes as $class) {
                $class = strtolower($class);
                if(empty(Registry::prototypes($class))) {
                    continue;
                }

                $methods = Registry::prototypes($class);
                $name = strtolower($name);
                if(array_key_exists($name,$methods)) {
                    $method = $methods[$name];

                    if(!is_array($method)) {
                        if($method instanceof \Closure) {
                            $method = $method->bindTo($scope,$scope);
                        }

                        // 99% will land here
                        return call_user_func_array($method,$arguments);
                    } else {
                        $results = [];
                        $c = 0;
                        foreach($method as $callback) {                
                            if($callback instanceof \Closure) {
                                $callback = $callback->bindTo($scope,$scope);
                            }

                            // 99% will land here
                            if(($result = call_user_func_array($callback,$arguments)) !== null) {
                                $results[] = $result;
                                $c++;
                            }
                        }

                        if($c === 1) {
                            return $results[0];
                        }

                        return $results;
                    }
                }
            }
        }
    }   
}