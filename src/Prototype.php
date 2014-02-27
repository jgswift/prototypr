<?php
namespace prototypr {
    /**
     * prototype trait
     * @package prototypr
     */
    trait Prototype {
        /**
         * Magic method is passed to manager
         * @param string $name
         * @param array $arguments
         * @return mixed
         */
        function __call($name,$arguments) {
            return Manager::call($this,$name,$arguments);
        }

        /**
         * Magic method is passed to manager
         * @param string $name
         * @param array $arguments
         * @return mixed
         */
        public static function __callStatic($name,$arguments) {
            return Manager::callStatic(get_called_class(),$name,$arguments);
        }
    }
}

