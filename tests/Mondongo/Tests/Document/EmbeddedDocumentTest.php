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

namespace Mondongo\Tests\Document;

use Mondongo\Tests\TestCase;
use Mondongo\Document\EmbeddedDocument as EmbeddedDocumentBase;
use Model\Article;
use Model\Comment;
use Model\Source;

class EmbeddedDocument extends EmbeddedDocumentBase
{
    protected $fieldsModified = array();

    protected $data = array(
        'fields' => array(
            'title' => 'Mondongus',
        ),
    );
}

class EmbeddedDocumentTest extends TestCase
{
    public function testIsModifiedFields()
    {
        $article = new Article();
        $this->assertFalse($article->isModified());
        $article->setTitle('Mondongo');
        $this->assertTrue($article->isModified());
    }

    public function testIsModifiedEmbeddedsOne()
    {
        $article = new Article();
        $source = new \Model\Source();
        $article->setSource($source);
        $this->assertFalse($article->isModified());

        $source->setName('Mondongo');
        $this->assertTrue($article->isModified());

        $article->clearModified();
        $article->setSource(null);
        $this->assertTrue($article->isModified());
    }

    public function testIsModifiedEmbeddedsMany()
    {
        $article = new Article();
        $comments = $article->getComments();
        $comments->add($comment = new Comment());
        $this->assertFalse($article->isModified());
        $comment->setName('Pablo');
        $this->assertTrue($article->isModified());
    }

    public function testClearModified()
    {
        $article = new Article();
        $article->setTitle('Mondongo');
        $article->setContent('Content');
        $this->assertTrue($article->isModified());
        $article->clearModified();
        $this->assertFalse($article->isModified());
    }

    public function testClearModifiedWithEmbeddeds()
    {
        $article = new Article();

        $source = new Source();
        $source->setName('Source');
        $article->setSource($source);

        $comment1 = new Comment();
        $comment1->setName('Foo');
        $article->getComments()->add($comment1);
        $comment2 = new Comment();
        $comment2->setName('Bar');
        $article->getComments()->add($comment2);

        $this->assertTrue($article->isModified());
        $this->assertTrue($source->isModified());
        $this->assertTrue($comment1->isModified());
        $this->assertTrue($comment2->isModified());

        $article->clearModified();

        $this->assertFalse($article->isModified());
        $this->assertFalse($source->isModified());
        $this->assertFalse($comment1->isModified());
        $this->assertFalse($comment2->isModified());
    }

    public function testIsFieldModified()
    {
        $source = new \Model\Source();
        $source->setFieldModified('name', 'foo');

        $this->assertTrue($source->isFieldModified('name'));
        $this->assertFalse($source->isFieldModified('url'));
    }

    public function testGetFieldModified()
    {
        $source = new \Model\Source();
        $source->setName('foo');
        $source->clearFieldsModified();
        $source->setName('bar');

        $this->assertSame('foo', $source->getFieldModified('name'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetFieldModifiedNotExists()
    {
        $source = new \Model\Source();
        $source->getFieldModified('name');
    }

    public function testSetFieldModified()
    {
        $source = new \Model\Source();
        $source->setFieldModified('name', 'foo');
        $this->assertSame('foo', $source->getFieldModified('name'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetFieldModifiedNotExist()
    {
        $source = new \Model\Source();
        $source->setFieldModified('no', 'foo');
    }

    public function testRemoveFieldModified()
    {
        $source = new \Model\Source();
        $source->setFieldModified('name', 'foo');
        $source->removeFieldModified('name');
        $this->assertFalse($source->isFieldModified('name'));
    }

    public function testGetFieldsModified()
    {
        $source = new \Model\Source();
        $source->setName('foo');
        $this->assertSame(array('name' => null), $source->getFieldsModified());

        $source->clearModified();
        $source->setName('bar');
        $this->assertSame(array('name' => 'foo'), $source->getFieldsModified());
    }

    public function testClearFieldsModified()
    {
        $source = new \Model\Source();
        $source->setName('foo');

        $source->clearFieldsModified();
        $this->assertSame(array(), $source->getFieldsModified());
    }

    public function testRevertFieldsModified()
    {
        $comment = new Comment();
        $comment->setName('Pablo');
        $comment->setText('Mondongo');
        $comment->clearFieldsModified();
        $comment->setName('pablodip');
        $comment->setText('Mondongus');
        $comment->revertFieldsModified();

        $this->assertSame('Pablo', $comment->getName());
        $this->assertSame('Mondongo', $comment->getText());
    }

    public function testGetDocumentData()
    {
        $document = new EmbeddedDocument();
        $this->assertSame(array('fields' => array('title' => 'Mondongus')), $document->getDocumentData());
    }

    public function testDataToMongo()
    {
        $article = new Article();
        $article->setTitle(123);
        $article->setContent(456);

        $this->assertSame(array(
            'title'   => '123',
            'content' => '456',
        ), $article->dataToMongo());
    }

    public function testDataToMongoWithEmbeddeds()
    {
        $article = new Article();
        $article->setTitle(123);

        $source = new Source();
        $source->setName(234);
        $source->setUrl('http://mondongo.es');
        $article->setSource($source);

        $comment1 = new Comment();
        $comment1->setName(345);
        $article->getComments()->add($comment1);
        $comment2 = new Comment();
        $comment2->setName('Pablo');
        $comment2->setText(456);
        $article->getComments()->add($comment2);

        $this->assertSame(array(
            'title'  => '123',
            'source' => array(
                'name' => '234',
                'url'  => 'http://mondongo.es',
            ),
            'comments' => array(
                array(
                    'name' => '345',
                ),
                array(
                    'name' => 'Pablo',
                    'text' => '456',
                ),
            ),
        ), $article->dataToMongo());
    }
}
