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

use Mondongo\Tests\PHPUnit\TestCase;
use Mondongo\Mondator\Extension;
use Mondongo\Mondator\Mondator;
use Mondongo\Mondator\Output;

class MondatorTest extends TestCase
{
    public function testConfigClasses()
    {
        $mondator = new Mondator();
        $mondator->setConfigClass('Article', $article = array(
            'title'   => 'string',
            'content' => 'string',
        ));
        $mondator->setConfigClass('Comment', $comment = array(
            'name' => 'string',
            'text' => 'string',
        ));

        $this->assertTrue($mondator->hasConfigClass('Article'));
        $this->assertFalse($mondator->hasConfigClass('Category'));

        $this->assertSame($article, $mondator->getConfigClass('Article'));
        $this->assertSame($comment, $mondator->getConfigClass('Comment'));

        $this->assertSame(array('Article' => $article, 'Comment' => $comment), $mondator->getConfigClasses());

        $mondator->setConfigClasses($classes = array(
            'Category' => array('name' => 'string'),
            'Post'     => array('message' => 'string'),
        ));
        $this->assertSame($classes, $mondator->getConfigClasses());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetConfigClassNotExists()
    {
        $mondator = new Mondator();
        $mondator->getConfigClass('Article');
    }

    public function testExtensions()
    {
        $extension1 = new ExtensionTesting();
        $extension2 = new ExtensionTesting();
        $extension3 = new ExtensionTesting();
        $extension4 = new ExtensionTesting();

        $mondator = new Mondator();

        $mondator->addExtension($extension1);
        $mondator->addExtension($extension2);
        $this->assertSame(array($extension1, $extension2), $mondator->getExtensions());

        $mondator->setExtensions($extensions = array($extension3, $extension4));
        $this->assertSame($extensions, $mondator->getExtensions());
    }

    public function testOutputs()
    {
        $output1 = new Output('output');
        $output2 = new Output('output');
        $output3 = new Output('output');
        $output4 = new Output('output');

        $mondator = new Mondator();

        $mondator->setOutput('output1', $output1);
        $mondator->setOutput('output2', $output2);
        $this->assertTrue($mondator->hasOutput('output1'));
        $this->assertFalse($mondator->hasOutput('output3'));
        $this->assertSame($output1, $mondator->getOutput('output1'));
        $this->assertSame($output2, $mondator->getOutput('output2'));
        $this->assertSame(array('output1' => $output1, 'output2' => $output2), $mondator->getOutputs());

        $mondator->setOutputs($outputs = array('output3' => $output3, 'output4' => $output4));
        $this->assertSame($outputs, $mondator->getOutputs());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOutputNotExists()
    {
        $mondator = new Mondator();
        $mondator->getOutput('output1');
    }

    public function testGenerateContainers()
    {
        $mondator = new Mondator();
        $mondator->setClassDefinitions(array(
            'Article' => array(
                'name' => 'foo',
            ),
            'Category' => array(
                'name' => 'bar',
            ),
        ));
        $mondator->setExtensions(array(
            new \Mondongo\Tests\Mondator\Fixtures\Extension\Name(),
            new \Mondongo\Tests\Mondator\Fixtures\Extension\InitDefinition(array(
                'definition_name' => 'myclass',
                'class_name'      => 'MiClase',
            )),
            new \Mondongo\Tests\Mondator\Fixtures\Extension\AddProperty(array(
                'definition' => 'myclass',
                'visibility' => 'public',
                'name'       => 'MiPropiedad',
                'value'      => 'foobar',
            )),
        ));

        $containers = $mondator->generateContainers();

        $this->assertSame(2, count($containers));
        $this->assertTrue(isset($containers['Article']));
        $this->assertTrue(isset($containers['Category']));
        $this->assertInstanceOf('Mondongo\Mondator\Definition\Container', $containers['Article']);
        $this->assertInstanceOf('Mondongo\Mondator\Definition\Container', $containers['Category']);

        $this->assertSame(2, count($containers['Article']->getDefinitions()));
        $this->assertTrue(isset($containers['Article']['name']));
        $this->assertTrue(isset($containers['Article']['myclass']));
        $this->assertSame('foo', $containers['Article']['name']->getClassName());

        $this->assertSame(2, count($containers['Category']->getDefinitions()));
        $this->assertTrue(isset($containers['Category']['name']));
        $this->assertTrue(isset($containers['Category']['myclass']));
        $this->assertSame('bar', $containers['Category']['name']->getClassName());
    }
}
