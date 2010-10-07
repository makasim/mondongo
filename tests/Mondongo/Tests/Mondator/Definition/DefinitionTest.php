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
use Mondongo\Mondator\Definition\Definition;
use Mondongo\Mondator\Definition\Method;
use Mondongo\Mondator\Definition\Property;

class DefinitionTest extends TestCase
{
    public function testNamespace()
    {
        $definition = new Definition();
        $definition->setNamespace('\Mondongo\Mondator\Definition');
        $this->assertSame('\Mondongo\Mondator\Definition', $definition->getNamespace());
    }

    public function testClassName()
    {
        $definition = new Definition();
        $definition->setClassName('FooBar');
        $this->assertSame('FooBar', $definition->getClassName());
    }

    public function testGetFullClass()
    {
        $definition = new Definition();
        $definition->setClassName('Document');
        $this->assertSame('Document', $definition->getFullClass());

        $definition->setNamespace('Model\\Documents');
        $this->assertSame('Model\\Documents\\Document', $definition->getFullClass());
    }

    public function testParentClass()
    {
        $definition = new Definition();
        $definition->setParentClass('ParentFooBar');
        $this->assertSame('ParentFooBar', $definition->getParentClass());
    }

    public function testInterfaces()
    {
        $definition = new Definition();
        $definition->addInterface('\ArrayAccess');
        $definition->addInterface('\Countable');
        $this->assertSame(array('\ArrayAccess', '\Countable'), $definition->getInterfaces());

        $definition->setInterfaces($interfaces = array('\ArrayObject', '\InfiniteIterador'));
        $this->assertSame($interfaces, $definition->getInterfaces());
    }

    public function testIsAbstract()
    {
        $definition = new Definition();
        $this->assertFalse($definition->getIsAbstract());
        $definition->setIsAbstract(true);
        $this->assertTrue($definition->getIsAbstract());
    }

    public function testProperties()
    {
        $property1 = new Property('public', 'property1');
        $property2 = new Property('public', 'property2');
        $property3 = new Property('public', 'property3');
        $property4 = new Property('public', 'property4');

        $definition = new Definition();
        $definition->addProperty($property1);
        $definition->addProperty($property2);
        $this->assertSame(array($property1, $property2), $definition->getProperties());

        $definition->setProperties(array($property3, $property4));
        $this->assertSame(array($property3, $property4), $definition->getProperties());
    }

    public function testMethods()
    {
        $method1 = new Method('public', 'method1', '', '');
        $method2 = new Method('public', 'method2', '', '');
        $method3 = new Method('public', 'method3', '', '');
        $method4 = new Method('public', 'method4', '', '');

        $definition = new Definition();
        $definition->addMethod($method1);
        $definition->addMethod($method2);
        $this->assertSame(array($method1, $method2), $definition->getMethods());

        $definition->setMethods(array($method3, $method4));
        $this->assertSame(array($method3, $method4), $definition->getMethods());
    }

    public function testPHPDoc()
    {
        $definition = new Definition();
        $definition->setPHPDoc('myDoc');
        $this->assertSame('myDoc', $definition->getPHPDoc());
    }
}
