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
    public function testClasses()
    {
        $mondator = new Mondator();
        $mondator->setClass('Article', $article = array(
            'title'   => 'string',
            'content' => 'string',
        ));
        $mondator->setClass('Comment', $comment = array(
            'name' => 'string',
            'text' => 'string',
        ));

        $this->assertTrue($mondator->hasClass('Article'));
        $this->assertFalse($mondator->hasClass('Category'));

        $this->assertSame($article, $mondator->getClass('Article'));
        $this->assertSame($comment, $mondator->getClass('Comment'));

        $this->assertSame(array('Article' => $article, 'Comment' => $comment), $mondator->getClasses());

        $mondator->setClasses($classes = array(
            'Category' => array('name' => 'string'),
            'Post'     => array('message' => 'string'),
        ));
        $this->assertSame($classes, $mondator->getClasses());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetClassNotExists()
    {
        $mondator = new Mondator();
        $mondator->getClass('Article');
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
        $mondator->getClass('output1');
    }
}
