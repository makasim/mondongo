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
    protected $mondongo;

    public function setUp()
    {
        Container::clear();
    }

    public function testSetGet()
    {
        Container::set($mondongo = new Mondongo());
        $this->assertSame($mondongo, Container::get());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetThereIsNotMondongo()
    {
        Container::get();
    }

    public function testGetWithLoader()
    {
        Container::setLoader(array($this, 'load'));
        $this->mondongo = new Mondongo();

        $this->assertSame($this->mondongo, Container::get());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetWithLoaderDoesNotReturnAMondongoInstance()
    {
        Container::setLoader(array($this, 'load'));
        $this->mondongo = 'ups';

        Container::get();
    }

    public function load()
    {
        return $this->mondongo;
    }

    public function testSetLoaderGetLoader()
    {
        Container::setLoader($loader = array($this, 'load'));

        $this->assertSame($loader, Container::getLoader());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testClear()
    {
        Container::set(new Mondongo());
        Container::clear();

        Container::get();
    }
}
