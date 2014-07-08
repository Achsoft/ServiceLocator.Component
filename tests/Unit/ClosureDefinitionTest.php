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
 * Unit test for closure definition.
 *
 * @author Achmad F. Ibrahim <acfatah@gmail.com>
 */
class ClosureDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->sc = new \Achsoft\Component\ServiceLocator\Container();
        
        $this->sc->register('string', 'foo');
        $this->sc->register('closure', function() {
            return 'foo';
        });
    }
    
    public function testClosureDefinition()
    {
        $sc = $this->sc;
        
        $sc->register('a', function($sc) {
            return $sc->get('string');
        });
        
        $sc->register('b', function() use ($sc) {
            return $sc->get('string');
        });
        
        $this->assertSame($sc->get('a'), $sc->get('b'));
        $this->assertEquals('foo', $sc->get('a'));
        $this->assertEquals('foo', $sc->get('b'));
        
        $sc->set('string', 'bar');
        
        $this->assertSame($sc->get('a'), $sc->get('b'));
        $this->assertEquals('bar', $sc->get('a'));
        $this->assertEquals('bar', $sc->get('b'));
    }
}
