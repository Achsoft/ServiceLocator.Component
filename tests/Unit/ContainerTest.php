<?php

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file.
 * Redistributions of files must retain the above copyright notice.
 * 
 * @copyright (c) 2014, Achmad F. Ibrahim
 * @link https://github.com/Achsoft
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */

namespace Test\Unit;

/**
 * ServiceLocator test class.
 *
 * @author Achmad F. Ibrahim <acfatah@gmail.com>
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Array configurations
        $config = require __DIR__ . '/../Fixture/config.php';
        
        // Service Locator instance
        $this->sc = new \Achsoft\Component\ServiceLocator\Container($config);
        
        // locked definition
        $this->sc->register('locked', function ($sl) {
            // some definition
        });
        $this->sc->lock('locked');
    }
    
    public function testRegisteredAndResolve()
    {
        $this->assertTrue($this->sc->has('first'));
        $this->assertTrue($this->sc->has('second'));
        $this->assertTrue($this->sc->has('dependant'));
        
        $dependant = $this->sc->resolve('dependant');
        
        $this->assertInstanceOf('\Test\Fixture\Dependant', $dependant);
        $this->assertInstanceOf('\Test\Fixture\FirstDependency', $dependant->first);
        $this->assertInstanceOf('\Test\Fixture\SecondDependency', $dependant->second);
    }
    
    public function testRegisterSameIdentifier()
    {
        $e = '\Achsoft\Component\ServiceLocator\Exception\InvalidIdentifierException';
        $this->setExpectedException($e);
        $this->sc->register('first', function ($sl){
            // some definition
        });
    }
    
    public function testRegisterVariables()
    {
        // register a string
        $this->sc->register('string', 'foo');
        // register an array
        $this->sc->register('array', ['foo', 'bar', 'baz']);
        // register an object
        $this->sc->register('object', new \stdClass());
        
        $this->assertSame('foo', $this->sc->resolve('string'));
        $this->assertSame(['foo', 'bar', 'baz'], $this->sc->resolve('array'));
        $this->assertInstanceOf('\stdClass', $this->sc->resolve('object'));
    }
    
    public function testRegisterClosureZeroParameter()
    {
        $this->sc->register('foo', function () {
            return 'foo';
        });
        $foo = $this->sc->resolve('foo');
        
        $this->assertEquals('foo', $foo);
    }
    
    public function testRegisterClosureInvalidNumberOfParameters()
    {
        $e = '\Achsoft\Component\ServiceLocator\Exception\InvalidDefinitionException';
        $this->setExpectedException($e);
        $this->sc->register('invalid', function ($one, $two, $three) {
            // some definition
        });
    }
    
    public function testResolveUnregisteredDefinition()
    {
        $e = '\Achsoft\Component\ServiceLocator\Exception\NotFoundException';
        $this->setExpectedException($e);
        $this->sc->resolve('something');
    }
    
    public function testUnregister()
    {
        $this->sc->unregister('first');
        $this->assertFalse($this->sc->has('first'));
    }
    
    public function testUnregisterLockedDefinition()
    {
        $e = '\Achsoft\Component\ServiceLocator\Exception\ProtectedDefinitionException';
        $this->setExpectedException($e);
        $this->sc->unregister('locked');
    }
    
    public function testLock()
    {
        $this->assertTrue($this->sc->has('locked'));
        $this->assertTrue($this->sc->locked('locked'));
    }
    
    public function testLockUnregisteredDefinition()
    {
        $e = '\Achsoft\Component\ServiceLocator\Exception\NotFoundException';
        $this->setExpectedException($e);
        $this->sc->lock('something');
    }
    
    /**
     * @depends testLock
     */
    public function testModifyLockedDefinition()
    {
        $e = '\Achsoft\Component\ServiceLocator\Exception\ProtectedDefinitionException';
        $this->setExpectedException($e);
        $this->sc->modify('locked', function ($sl) {
            // new definition
        });
    }
    
    public function testUnlock()
    {
        // lock a definition
        $this->sc->unlock('locked');
        $this->assertFalse($this->sc->locked('locked'));
        
        // modify
        $this->sc->modify('locked', function ($sl) {
            return 'foo';
        });
        
        $this->assertEquals('foo', $this->sc->resolve('locked'));
    }
    
    public function testUnlockUnregisteredDefinition()
    {
        $e = '\Achsoft\Component\ServiceLocator\Exception\NotFoundException';
        $this->setExpectedException($e);
        $this->sc->unlock('something');
    }
    
    /**
     * @depends testRegisteredAndResolve
     */
    public function testExtend()
    {
        $this->sc->extend('dependant', function ($sl, $d) {
            $d->first = new \Test\Fixture\ThirdDependency();
            return $d;
        });
        
        $dependant = $this->sc->resolve('dependant');
        $this->assertInstanceOf('\Test\Fixture\ThirdDependency', $dependant->first);
    }
    
    public function testExtendStringClassname()
    {
        $this->sc->extend('first', function ($sl, $f) {
            $f->foo = 'foo';
            return $f;
        });
        
        $first = $this->sc->resolve('first');
        $this->assertEquals('foo', $first->foo);
    }
    
    public function testExtendUnregisteredDefinition()
    {
        $e = '\Achsoft\Component\ServiceLocator\Exception\NotFoundException';
        $this->setExpectedException($e);
        $this->sc->extend('something', function ($sl, $s) {
            // some definition
        });
    }
    
    public function testExtendLockedDefinition()
    {
        $e = '\Achsoft\Component\ServiceLocator\Exception\ProtectedDefinitionException';
        $this->setExpectedException($e);
        $this->sc->extend('locked', function ($sl, $l) {
            // some definition
        });
    }
    
    public function testExtendInvalidClosure()
    {
        $e = '\Achsoft\Component\ServiceLocator\Exception\InvalidDefinitionException';
        $this->setExpectedException($e);
        $this->sc->extend('first', function () {
            // do not have two parameters
        });
    }
    
    public function testRegisterAs()
    {
        $this->sc->registerAs('tenth', 'first');
        $this->assertInstanceOf('\Test\Fixture\FirstDependency', $this->sc->resolve('tenth'));
    }
    
    /**
     * @depends testExtend
     */
    public function testRegisterAsAndExtend()
    {
        $this->sc->registerAs('tenth', 'first', function ($sl, $f) {
            $f->bar = 'bar';
            return $f;
        });
        
        $tenth = $this->sc->resolve('tenth');
        $this->assertEquals('bar', $tenth->bar);
    }
    
    public function testRegisterAsRegisteredNewId()
    {
        $e = '\Achsoft\Component\ServiceLocator\Exception\InvalidIdentifierException';
        $this->setExpectedException($e);
        $this->sc->registerAs('first', 'second');
    }
    
    public function testRegisterAsUnregisteredId()
    {
        $e = '\Achsoft\Component\ServiceLocator\Exception\NotFoundException';
        $this->setExpectedException($e);
        $this->sc->registerAs('tenth', 'something');
    }
}
