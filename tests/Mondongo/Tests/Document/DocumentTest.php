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
use Mondongo\Document\Document as DocumentBase;
use Model\Article;
use Model\Author;
use Model\Comment;
use Model\Source;
use Model\MultipleEmbeds;
use Model\MultipleEmbedsEmbedded1;
use Model\MultipleEmbedsEmbedded2;

class Document extends DocumentBase
{
}

class DocumentTest extends TestCase
{
    public function testGetCollection()
    {
        $article = new Article();

        $this->assertSame($article->getRepository()->getCollection(), $article->getCollection());
    }

    public function testId()
    {
        $document = new Document();
        $this->assertNull($document->getId());
        $document->setId($id = new \MongoId('123'));
        $this->assertSame($id, $document->getId());
    }

    public function testIsNew()
    {
        $document = new Document();
        $this->assertTrue($document->isNew());
        $document->setId(new \MongoId('123'));
        $this->assertFalse($document->isNew());
    }

    public function testRefresh()
    {
        $article = new Article();
        $article->setTitle('My Title');
        $article->setContent('My Content');
        $article->save();

        $article->setTitle('foo');
        $article->setContent('bar');
        $article->refresh();
        $this->assertSame('My Title', $article->getTitle());
        $this->assertSame('My Content', $article->getContent());
    }

    public function testRefreshChangedValues()
    {
        $article = new Article();
        $article->setTitle('foo');
        $article->setContent('bar');
        $article->save();

        $article->setTitle('Ups');
        $article->refresh();
        $this->assertSame('foo', $article->getTitle());
    }

    public function testRefreshDeletingValues()
    {
        $article = new Article();
        $article->setTitle('foo');
        $article->setContent('bar');
        $article->save();

        $article->getCollection()->update(array('_id' => $article->getId()), array('$unset' => array('content' => 1)));

        $article->refresh();
        $this->assertNull($article->getContent());
    }

    public function testRefreshWithReferences()
    {
        $author1 = new Author();
        $author1->setName('Pablo');
        $author1->save();

        $author2 = new Author();
        $author2->setName('pablodip');
        $author2->save();

        $article = new Article();
        $article->setTitle('Mon');
        $article->setAuthor($author1);
        $article->save();

        $article->getCollection()->update(array('_id' => $article->getId()), array('author_id' => $author2->getId()));

        $article->refresh();
        $this->assertEquals($author2->getId(), $article->getAuthorId());
        $this->assertSame($author2, $article->getAuthor());
    }

    public function testRefreshWithEmbeddeds()
    {
        $article = new Article();
        $article->setTitle('My Title');
        $article->getSource()->setName('My Name');
        $article->save();

        $article->setTitle('foo');
        $article->getSource()->setName('bar');
        $article->refresh();
        $this->assertSame('My Title', $article->getTitle());
        $this->assertSame('My Name', $article->getSource()->getName());
    }

    public function testRefreshWithEmbeddedsDeleting()
    {
        $article = new Article();
        $article->setTitle('My Title');
        $article->getSource()->setName('My Name');
        $article->save();

        $article->getCollection()->update(array('_id' => $article->getId()), array('$unset' => array('source' => 1)));

        $article->refresh();
        $this->assertEquals(new Source(), $article->getSource());
    }

    /**
     * @expectedException \LogicException
     */
    public function testRefreshIsNew()
    {
        $article = new Article();
        $article->setTitle('foo');
        $article->refresh();
    }

    public function testSave()
    {
        $article = new Article();
        $article->setTitle('$document->save()');
        $article->save();
        $this->assertEquals($article, $article->getRepository()->findOneById($article->getId()));
    }

    public function testDelete()
    {
        $article = new Article();
        $article->setTitle('$document->delete()');
        $article->save();
        $article->delete();
        $this->assertNull($article->getRepository()->findOneById($article->getId()));
    }

    public function testQueryForSaveInsert()
    {
        $article = new Article();
        $this->assertSame(array(), $article->getQueryForSave());

        $article->setTitle(1);
        $article->setIsActive(1);
        $this->assertSame(array('title' => '1', 'is_active' => true), $article->getQueryForSave());
    }

    public function testQueryForSaveUpdate()
    {
        $article = new Article();
        $article->setTitle('Mondongo');
        $article->setContent('Content');
        $article->setIsActive(false);
        $article->save();

        $article->setTitle('Mondongo Updated');
        $article->setContent(null);
        $article->setIsActive(1);

        $this->assertSame(array(
            '$set' => array(
                'title'     => 'Mondongo Updated',
                'is_active' => true,
            ),
            '$unset' => array(
                'content' => 1,
            ),
        ), $article->getQueryForSave());
    }

    public function testQueryForSaveInsertEmbeddeds()
    {
        $article = new Article();
        $article->setTitle(123);

        $source = new Source();
        $source->setName(345);
        $article->setSource($source);

        $comment = new Comment();
        $comment->setName(1.34);
        $article->getComments()->add($comment);
        $comment = new Comment();
        $comment->setName(145);
        $article->getComments()->add($comment);

        $this->assertSame(array(
            'title' => '123',
            'source' => array(
                'name' => '345'
            ),
            'comments' => array(
                array(
                    'name' => '1.34',
                ),
                array(
                    'name' => '145',
                ),
            ),
        ), $article->getQueryForSave());
    }

    public function testQueryForSaveUpdateEmbeddeds()
    {
        $article = new Article();
        $article->setId(new \MongoId('123'));

        $article->setTitle('Mondongo');
        $article->setContent('Content');

        $source = new Source();
        $source->setName('Source Name');
        $source->setUrl('http://mondongo.es');
        $article->setSource($source);

        $comment1 = new Comment();
        $comment1->setName('Comment 1');
        $comment1->setText('Foo');
        $article->getComments()->add($comment1);
        $comment2 = new Comment();
        $comment2->setName('Comment 2');
        $comment2->setText('Bar');
        $article->getComments()->add($comment2);

        $article->clearModified();
        $article->getComments()->saveOriginalElements();

        $article->setTitle(123);
        $article->setContent(null);
        $source->setName(456);
        $source->setUrl(null);
        $comment1->setText(789);
        $comment2->setName(null);

        $this->assertSame(array(
            '$set' => array(
                'title'         => '123',
                'source.name'     => '456',
                'comments.0.text' => '789',
            ),
            '$unset' => array(
                'content'           => 1,
                'source.url'      => 1,
                'comments.1.name' => 1,
            ),
        ), $article->getQueryForSave());
    }

    public function testQueryForSaveUpdateEmbeddedsPushAllPullAll()
    {
        $article = new Article();
        $article->setId(new \MongoId('123'));

        $comments = array();
        for ($i = 1; $i <= 10; $i++) {
            $comments[$i] = $comment = new Comment();
            $comment->setName($i);
        }
        $article->getComments()->setElements($comments);

        $article->clearModified();
        $article->getComments()->saveOriginalElements();

        $article->getComments()->remove($comments[3]);
        $article->getComments()->remove($comments[5]);

        $comment11 = new Comment();
        $comment11->setName(11);
        $article->getComments()->add($comment11);
        $comment12 = new Comment();
        $comment12->setName(12);
        $article->getComments()->add($comment12);

        $this->assertSame(array(
            '$pushAll' => array(
                'comments' => array(
                    array(
                        'name' => '11',
                    ),
                    array(
                        'name' => '12',
                    ),
                ),
            ),
            '$pullAll' => array(
                'comments' => array(
                    array(
                        'name' => '3',
                    ),
                    array(
                        'name' => '5',
                    ),
                ),
            ),
        ), $article->getQueryForSave());
    }

    public function testQueryForSaveMultipleEmbeds()
    {
        $document = new MultipleEmbeds();
        $document->setTitle('My Title');

        $document->getEmbeddeds1()->add($e1 = new MultipleEmbedsEmbedded1());
        $e1->setName('My Name 1');

        $e1->getEmbeddeds2()->add($e2 = new MultipleEmbedsEmbedded2());
        $e2->setField1('My Field 1');

        $this->assertSame(array(
            'title' => 'My Title',
            'embeddeds1' => array(
                array(
                    'name' => 'My Name 1',
                    'embeddeds2' => array(
                        array(
                            'field1' => 'My Field 1',
                        ),
                    ),
                ),
            ),
        ), $document->getQueryForSave());
    }
}
