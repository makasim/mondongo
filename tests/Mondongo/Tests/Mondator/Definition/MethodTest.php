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

namespace Mondongo\Tests\Mondator\Definition;

use Mondongo\Tests\PHPUnit\TestCase;
use Mondongo\Mondator\Definition\Method;

class MethodTest extends TestCase
{
    public function testConstructor()
    {
        $method = new Method('public', 'setVisibility', '$visibility', '$this->visibility = $visibility;', true, false);

        $this->assertSame('public',  $method->getVisibility());
        $this->assertSame('setVisibility', $method->getName());
        $this->assertSame('$visibility', $method->getArguments());
        $this->assertSame('$this->visibility = $visibility;', $method->getCode());
        $this->assertTrue($method->getIsStatic());
        $this->assertFalse($method->getIsAbstract());
    }

    public function testVisibility()
    {
        $method = new Method('public', 'setVisibility', '$visibility', '$this->visibility = $visibility;');

        $method->setVisibility('protected');
        $this->assertSame('protected', $method->getVisibility());
    }

    public function testName()
    {
        $method = new Method('public', 'setVisibility', '$visibility', '$this->visibility = $visibility;');

        $method->setName('setV');
        $this->assertSame('setV', $method->getName());
    }

    public function testArguments()
    {
        $method = new Method('public', 'setVisibility', '$visibility', '$this->visibility = $visibility;');

        $method->setArguments('$v');
        $this->assertSame('$v', $method->getArguments());
    }

    public function testCode()
    {
        $method = new Method('public', 'setVisibility', '$visibility', '$this->visibility = $visibility;');

        $method->setCode('$this->visibility = $v;');
        $this->assertSame('$this->visibility = $v;', $method->getCode());
    }

    public function testIsStatic()
    {
        $method = new Method('public', 'setVisibility', '$visibility', '$this->visibility = $visibility;');

        $this->assertFalse($method->getIsStatic());
        $method->setIsStatic(true);
        $this->assertTrue($method->getIsStatic());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetIsStaticNotBoolean()
    {
        $method = new Method('public', 'setVisibility', '$visibility', '$this->visibility = $visibility;');
        $method->setIsStatic(1);
    }

    public function testIsAbstract()
    {
        $method = new Method('public', 'setVisibility', '$visibility', '$this->visibility = $visibility;');

        $this->assertFalse($method->getIsAbstract());
        $method->setIsAbstract(true);
        $this->assertTrue($method->getIsAbstract());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetIsAbstractNotBoolean()
    {
        $method = new Method('public', 'setVisibility', '$visibility', '$this->visibility = $visibility;');
        $method->setIsAbstract(1);
    }

    public function testPHPDoc()
    {
        $method = new Method('public', 'setVisibility', '$visibility', '$this->visibility = $visibility;');

        $method->setPHPDoc('myDoc');
        $this->assertSame('myDoc', $method->getPHPDoc());
    }
}
