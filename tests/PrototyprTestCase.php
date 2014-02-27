<?php
namespace prototypr\Tests {
    use prototypr;
    /**
    * Base Prototypr test case class
    * Class PrototyprTestCase
    * @package Prototypr
    */
    abstract class PrototyprTestCase extends \PHPUnit_Framework_TestCase {
        /**
        * Perform setUp tasks
        */
        protected function setUp()
        {
        }

        /**
         * Perform clean up / tear down tasks
         */
        protected function tearDown()
        {
            prototypr\Registry::unregister('prototypr\Tests\Mock\User');
        }
    }
}