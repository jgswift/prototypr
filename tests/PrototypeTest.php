<?php
namespace prototypr\Tests {
    use prototypr;
    
    /**
     * @package prototypr
     */
    class PrototypeTest extends PrototyprTestCase {
        function testLocallyAssignedPrototype() {
            $user = new Mock\User();
            
            $c = 0;
            $user->isAllowed(function()use(&$c) {
                $c++;
            });
            
            $user->isAllowed();
            
            $this->assertEquals(1,$c);
        }
        
        function testGloballyAssignedPrototype() {
            $user = new Mock\User();
            
            $c = 0;
            Mock\User::isAllowed(function()use(&$c) {
               $c++; 
            });
            
            $user->isAllowed();
            
            $this->assertEquals(1,$c);
        }
        
        function testPrototypeExtension() {
            $user = new Mock\User();
            $customer = new Mock\Customer();
            
            $c = 0;
            Mock\User::isAllowed(function()use(&$c) {
                $c++;
            });
            
            prototypr\Manager::extend($customer,$user);
            
            $customer->isAllowed();
            
            $this->assertEquals(1,$c);
        }
        
        function testMultipleMethodPrototype() {
            $customer = new Mock\Customer();
            
            $c = 0;
            
            Mock\Customer::loginAttempt(function()use(&$c) {
               $c++; 
               return true;
            });
            
            Mock\Customer::loginAttempt(function()use(&$c) {
               $c+=2;
               return false;
            });
            
            $customer->loginAttempt();
            
            $this->assertEquals(3,$c);
        }
        
        /**
         * @expectedException prototypr\Exception
         */
        function testMethodInvalidException() {
            $customer = new Mock\Customer();
            
            $customer->nonExistantMethod();
        }
    }
}
