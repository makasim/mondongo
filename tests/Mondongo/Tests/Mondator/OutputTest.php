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

namespace Mondongo\Tests\Mondator;

use Mondongo\Mondator\Output;

class OutputTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $output = new Output('foo', true);
        $this->assertEquals('foo', $output->getDirectory());
        $this->assertTrue($output->getOverride());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorOverrideNotBoolean()
    {
        new Output('foo', 1);
    }

    public function testDirectory()
    {
        $output = new Output('foo');
        $this->assertEquals('foo', $output->getDirectory());
        $output->setDirectory('bar');
        $this->assertEquals('bar', $output->getDirectory());
    }

    public function testOverride()
    {
        $output = new Output('foo');
        $this->assertFalse($output->getOverride());
        $output->setOverride(true);
        $this->assertTrue($output->getOverride());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetOverrideNotBoolean()
    {
        $output = new Output('foo');
        $output->setOverride(1);
    }
}
