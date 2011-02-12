<?php

/*
 * Copyright 2010 Pablo Díez Pascual <pablodip@gmail.com>
 *
 * This file is part of Mondongo.
 *
 * Mondongo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mondongo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Mondongo. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mondongo\Tests;

use Mondongo\Container;
use Mondongo\Mondongo;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Container::clear();
    }

    public function testSetGetHasRemove()
    {
        Container::set('foo', $foo = new Mondongo());
        Container::set('bar', $bar = new Mondongo());

        $this->assertTrue(Container::has('foo'));
        $this->assertTrue(Container::has('bar'));
        $this->assertFalse(Container::has('foobar'));

        $this->assertSame($foo, Container::get('foo'));
        $this->assertSame($bar, Container::get('bar'));

        Container::remove('foo');

        $this->assertFalse(Container::has('foo'));
        $this->assertTrue(Container::has('bar'));
    }

    public function testGetDefaultName()
    {
        Container::set('foo', $foo = new Mondongo());
        Container::set('bar', $bar = new Mondongo());

        Container::setDefaultName('bar');

        $this->assertSame($bar, Container::get());
    }

    public function testGetWithLoader()
    {
        $foo = new Mondongo();
        $bar = new Mondongo();

        Container::setLoader('foo', function() use ($foo) { return $foo; });
        Container::setLoader('bar', function() use ($bar) { return $bar; });

        $this->assertSame($foo, Container::get('foo'));
        $this->assertSame($bar, Container::get('bar'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetNotExist()
    {
        Container::set('foo', new Mondongo());

        Container::get('bar');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetWithLoaderNotReturnMondongoInstance()
    {
        Container::setLoader('foo', function() { return 'ups'; });

        Container::get('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveNotExist()
    {
        Container::set('foo', new Mondongo());

        Container::remove('bar');
    }

    public function testDefaultName()
    {
        $this->assertFalse(Container::hasDefaultName());
        $this->assertNull(Container::getDefaultName());

        Container::setDefaultName('foo');

        $this->assertTrue(Container::hasDefaultName());
        $this->assertSame('foo', Container::getDefaultName());
    }

    public function testSetGetHasRemoveLoader()
    {
        Container::setLoader('foo', $foo = function() { });
        Container::setLoader('bar', $bar = function() { });

        $this->assertTrue(Container::hasLoader('foo'));
        $this->assertTrue(Container::hasLoader('bar'));
        $this->assertFalse(Container::hasLoader('foobar'));

        $this->assertSame($foo, Container::getLoader('foo'));
        $this->assertSame($bar, Container::getLoader('bar'));

        Container::removeLoader('foo');

        $this->assertFalse(Container::hasLoader('foo'));
        $this->assertTrue(Container::hasLoader('bar'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetLoaderNotExist()
    {
        Container::setLoader('foo', $foo = function() { });

        Container::getLoader('bar');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveLoaderNotExist()
    {
        Container::setLoader('foo', $foo = function() { });

        Container::removeLoader('bar');
    }

    public function testClear()
    {
        Container::set('foo', new Mondongo());
        Container::setDefaultName('foo');
        Container::setLoader('foo', function() { });

        Container::clear();

        $this->assertFalse(Container::has('foo'));
        $this->assertFalse(Container::hasDefaultName());
        $this->assertFalse(Container::hasLoader('foo'));
    }
}
