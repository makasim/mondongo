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
use Mondongo\Mondator\Definition\Container;
use Mondongo\Mondator\Definition\Definition;

class ContainerTest extends TestCase
{
    public function testDefinitions()
    {
        $definitionDocument   = new Definition('Class1');
        $definitionRepository = new Definition('Class2');
        $definitionMore       = new Definition('Class3');
        $definitionTest       = new Definition('Class4');

        $container = new Container();
        $this->assertFalse($container->hasDefinition('document'));
        $container->setDefinition('document', $definitionDocument);
        $this->assertTrue($container->hasDefinition('document'));
        $container->setDefinition('repository', $definitionRepository);

        $this->assertSame($definitionDocument, $container->getDefinition('document'));
        $this->assertSame($definitionRepository, $container->getDefinition('repository'));
        $this->assertSame(array(
            'document'   => $definitionDocument,
            'repository' => $definitionRepository,
        ), $container->getDefinitions());

        $container->setDefinitions($definitions = array('more' => $definitionMore, 'test' => $definitionTest));
        $this->assertSame($definitions, $container->getDefinitions());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetDefinitionNotExists()
    {
        $container = new Container();
        $container->getDefinition('document');
    }

    public function testArrayAccessInterface()
    {
        $definition1 = new Definition('Class1');
        $definition2 = new Definition('Class2');

        $container = new Container();
        $this->assertFalse(isset($container['definition1']));
        $container['definition1'] = $definition1;
        $container['definition2'] = $definition2;
        $this->assertTrue(isset($container['definition1']));
        $this->assertSame($definition1, $container['definition1']);
        $this->assertSame($definition2, $container['definition2']);
    }

    /**
     * @expectedException \LogicException
     */
    public function testArrayAccessInterfaceUnsetDisabled()
    {
        $container = new Container();
        unset($container['definition1']);
    }
}
