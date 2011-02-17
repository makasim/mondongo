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

use Mondongo\Connection;
use Mondongo\Mondongo;
use Mondongo\Repository as RepositoryBase;
use Mondongo\Group;
use Model\Article;
use Model\Author;
use Model\Category;
use Model\Events;
use Model\Image;
use Model\Message;

class Repository extends RepositoryBase
{
    protected $documentClass = 'User';
    protected $connectionName = 'default';
    protected $collectionName = 'users';
}

class RepositoryTest extends TestCase
{
    public function testGetMondongo()
    {
        $repository = new Repository($this->mondongo);

        $this->assertSame($this->mondongo, $repository->getMondongo());
    }

    public function testGetIdentityMap()
    {
        $repository = new Repository($this->mondongo);

        $identityMap = $repository->getIdentityMap();
        $this->assertInstanceOf('\Mondongo\IdentityMap', $identityMap);
        $this->assertSame($identityMap, $repository->getIdentityMap());
    }

    public function testGetDocumentClass()
    {
        $repository = new Repository($this->mondongo);

        $this->assertSame('User', $repository->getDocumentClass());
    }

    public function testGetConnectionName()
    {
        $repository = new Repository($this->mondongo);

        $this->assertSame('default', $repository->getConnectionName());
    }

    public function testGetCollectionName()
    {
        $repository = new Repository($this->mondongo);

        $this->assertSame('users', $repository->getCollectionName());
    }

    public function testGetConnection()
    {
        $mondongo = new Mondongo($this->metadata);
        $mondongo->setConnections(array(
            'local'  => $local  = new Connection('localhost', 'mondongo_tests_local'),
            'global' => $global = new Connection('localhost', 'mondongo_tests_global'),
        ));
        $mondongo->setDefaultConnectionName('local');

        $this->assertSame($local, $mondongo->getRepository('Model\Article')->getConnection());
        $this->assertSame($global, $mondongo->getRepository('Model\ConnectionGlobal')->getConnection());
    }

    public function testCollection()
    {
        $mondongo = new Mondongo($this->metadata);
        $connection = new Connection($this->server, $this->dbName);
        $mondongo->setConnection('default', $connection);
        $mondongo->setDefaultConnectionName('default');

        $collection = $mondongo->getRepository('Model\Article')->collection();

        $this->assertSame('MongoCollection', get_class($collection));
        $this->assertSame('article', $collection->getName());

        $connection->getMongo()->close();
    }

    public function testCollectionLoggable()
    {
        $mondongo = new Mondongo($this->metadata, $loggerCallable = function() {});
        $connection = new Connection($this->server, $this->dbName);
        $mondongo->setConnection('default', $connection);
        $mondongo->setDefaultConnectionName('default');

        $collection = $mondongo->getRepository('Model\Article')->collection();

        $this->assertSame('Mondongo\Logger\LoggableMongoCollection', get_class($collection));
        $this->assertSame('article', $collection->getName());

        $connection->getMongo()->close();
    }

    public function testCollectionGridFS()
    {
        $mondongo = new Mondongo($this->metadata);
        $connection = new Connection($this->server, $this->dbName);
        $mondongo->setConnection('default', $connection);
        $mondongo->setDefaultConnectionName('default');

        $collection = $mondongo->getRepository('Model\Image')->collection();

        $this->assertSame('MongoGridFS', get_class($collection));
        $this->assertSame('image.files', $collection->getName());

        $connection->getMongo()->close();
    }

    public function testCollectionGridFSLoggable()
    {
        $mondongo = new Mondongo($this->metadata, $loggerCallable = function() {});
        $connection = new Connection($this->server, $this->dbName);
        $mondongo->setConnection('default', $connection);
        $mondongo->setDefaultConnectionName('default');

        $collection = $mondongo->getRepository('Model\Image')->collection();

        $this->assertSame('Mondongo\Logger\LoggableMongoGridFS', get_class($collection));
        $this->assertSame('image.files', $collection->getName());

        $connection->getMongo()->close();
    }

    public function testQuery()
    {
        $repository = \Model\Article::repository();

        $query = $repository->query();
        $this->assertInstanceOf('Mondongo\Query', $query);
        $this->assertSame($repository, $query->getRepository());

        $this->assertNotSame($query, $repository->query());

        $criteria = array('is_active' => true);
        $this->assertSame($criteria, $repository->query($criteria)->getCriteria());
    }

    public function testFind()
    {
        $repository = $this->mondongo->getRepository('Model\Article');
        $articles   = $this->createArticles(10);

        $this->assertEquals($articles[2], $repository->find($articles[2]->getId()));
        $this->assertEquals($articles[5], $repository->find($articles[5]->getId()));

        $this->assertEquals($articles[2], $repository->find($articles[2]->getId()->__toString()));
        $this->assertEquals($articles[5], $repository->find($articles[5]->getId()->__toString()));

        $this->assertNull($repository->find(new \MongoId('123')));
    }

    public function testFindIdentityMap()
    {
        $repository = $this->mondongo->getRepository('Model\Article');
        $articles   = $this->createArticles(10);

        $this->assertEquals($articles[3], $article = $repository->find($articles[3]->getId()));
        $this->assertNotSame($articles[3], $article);

        $this->assertSame($article, $repository->find($articles[3]->getId()));
    }

    public function testCount()
    {
        $repository = $this->mondongo->getRepository('Model\Article');
        $articles   = $this->createArticles(10);

        $this->assertSame(10, $repository->count());
    }

    public function testCountQuery()
    {
        $repository = $this->mondongo->getRepository('Model\Article');
        $articles   = $this->createArticles(10);

        for ($i = 1; $i <= 5; $i++) {
            $articles[$i]->setTitle('Count');
            $repository->save($articles[$i]);
        }

        $this->assertSame(5, $repository->count(array('title' => 'Count')));
    }

    public function testRemove()
    {
        $repository = $this->mondongo->getRepository('Model\Article');
        $articles   = $this->createArticles(10);

        $this->assertTrue(is_array($repository->remove()));

        $this->assertSame(0, $this->db->article->find()->count());
    }

    public function testRemoveQuery()
    {
        $repository = $this->mondongo->getRepository('Model\Article');
        $articles   = $this->createArticles(10);

        $articles[3]->setTitle('No');
        $articles[3]->save();

        $this->assertTrue(is_array($repository->remove(array('title' => new \MongoRegex('/^Article/')))));

        $this->assertSame(1, $this->db->article->find()->count());
        $this->assertSame(1, $this->db->article->find(array('_id' => $articles[3]->getId()))->count());
    }

    public function testSaveInsertUnique()
    {
        $repository = $this->mondongo->getRepository('Model\Article');

        $article = new Article();
        $article->setTitle('Mondongo');
        $repository->save($article);

        $this->assertSame(1, $this->db->article->find()->count());

        $result = $this->db->article->findOne();

        $this->assertEquals($article->getId(), $result['_id']);
        $this->assertEquals('Mondongo', $result['title']);
    }

    public function testSaveUpdateUnique()
    {
        $repository = $this->mondongo->getRepository('Model\Article');
        $articles   = $this->createArticles(10);

        $articles[4]->setTitle('Mondongo Updated');
        $repository->save($articles[4]);
        $this->assertEquals(array(
            '_id'     => $articles[4]->getId(),
            'title'   => 'Mondongo Updated',
            'content' => 'Content',
        ), $this->db->article->findOne(array('_id' => $articles[4]->getId())));

        $this->assertSame(9, $this->db->article->find(array('title' => new \MongoRegex('/^Article/')))->count());
    }

    public function testSaveInsertWithSaveReferences()
    {
        $author = new Author();
        $author->setName('Pablo');

        $categories = new Group();
        for ($i = 1; $i <= 8; $i++) {
            $categories->add($category = new Category());
            $category->setName('Category '.$i);
            if ($i % 2) {
                $category->save();
            }
        }

        $article = new Article();
        $article->setTitle('Mondongo');
        $article->setAuthor($author);
        $article->setCategories($categories);

        $article->save();

        $this->assertFalse($article->isNew());
        $this->assertFalse($author->isNew());
        foreach ($categories as $category) {
            $this->assertFalse($category->isNew());
        }
    }

    public function testSaveUpdateWithSaveReferences()
    {
        $author = new Author();
        $author->setName('Pablo');

        $categories = new Group();
        for ($i = 1; $i <= 8; $i++) {
            $categories->add($category = new Category());
            $category->setName('Category '.$i);
            if ($i % 2) {
                $category->save();
            }
        }

        $article = new Article();
        $article->setTitle('Mondongo');
        $article->save();
        $article->setAuthor($author);
        $article->setCategories($categories);

        $article->save();

        $this->assertFalse($article->isNew());
        $this->assertFalse($author->isNew());
        foreach ($categories as $category) {
            $this->assertFalse($category->isNew());
        }
    }

    public function testSaveWithSaveReferencesReferingToHimself()
    {
        $messages = array();

        $messages[1] = $message = new Message();
        $message->setAuthor('pablodip');

        $messages[2] = $message = new Message();
        $message->setAuthor('Pablo');
        $message->setReplyTo($messages[1]);

        $repository = $this->mondongo->getRepository('Model\Message');

        $repository->save($messages);

        $this->assertSame(2, $repository->count());
    }

    public function testSaveWithEmbeddedsSaveOriginalElements()
    {
        $article = new \Model\Article();
        $article->setTitle('foo');
        $article->save();

        $comments = array();
        for ($i=1; $i <= 10; $i++) {
            $comments[] = $comment = new \Model\Comment();
            $comment->setName('name'.$i);
            $article->getComments()->add($comment);
            $article->save();
        }

        $articleRaw = \Model\Article::collection()->findOne(array('_id' => $article->getId()));
        $this->assertSame(10, count($articleRaw['comments']));
    }

    public function testSaveInsertGridFSSaveFile()
    {
        $file = __DIR__.'/MondongoTest.php';

        $repository = $this->mondongo->getRepository('Model\Image');

        $image = new Image();
        $image->setFile($file);
        $image->setName('Mondongo');
        $image->setDescription('Foobar');
        $repository->save($image);

        $result = $this->db->getGridFS('image')->findOne();

        $this->assertEquals($result->file['_id'], $image->getId());
        $this->assertSame(file_get_contents($file), $result->getBytes());
        $this->assertSame('Mondongo', $result->file['name']);
        $this->assertSame('Foobar', $result->file['description']);
    }

    public function testSaveInsertGridFSSaveBytes()
    {
        $bytes = file_get_contents(__DIR__.'/MondongoTest.php');

        $repository = $this->mondongo->getRepository('Model\Image');

        $image = new Image();
        $image->setFile($bytes);
        $image->setName('Mondongo');
        $image->setDescription('Foobar');
        $repository->save($image);

        $result = $this->db->getGridFS('image')->findOne();

        $this->assertEquals($result->file['_id'], $image->getId());
        $this->assertSame($bytes, $result->getBytes());
        $this->assertEquals('Mondongo', $image->getName());
        $this->assertEquals('Foobar', $image->getDescription());
    }

    public function testSaveUpdate()
    {
        $file = __DIR__.'/MondongoTest.php';

        $repository = $this->mondongo->getRepository('Model\Image');

        $image = new Image();
        $image->setFile($file);
        $image->setName('Mondongo');
        $image->setDescription('Foobar');
        $repository->save($image);

        $image->setName('GridFS');
        $image->setDescription('Rocks');
        $repository->save($image);

        $result = $this->db->getGridFS('image')->findOne();

        $this->assertEquals($result->file['_id'], $image->getId());
        $this->assertSame(file_get_contents($file), $result->getBytes());
        $this->assertEquals('GridFS', $image->getName());
        $this->assertEquals('Rocks', $image->getDescription());
    }

    public function testSaveEvents()
    {
        $repository = $this->mondongo->getRepository('Model\Events');

        $document = new Events();
        $document->setName('Mondongo');
        $repository->save($document);

        $this->assertSame(array(
            'preInsertExtensions',
            'preInsert',
            'preSaveExtensions',
            'preSave',
            'postInsertExtensions',
            'postInsert',
            'postSaveExtensions',
            'postSave',
        ), $document->getEvents());

        $document->clearEvents();
        $document->setName('Pablo');
        $repository->save($document);

        $this->assertSame(array(
            'preUpdateExtensions',
            'preUpdate',
            'preSaveExtensions',
            'preSave',
            'postUpdateExtensions',
            'postUpdate',
            'postSaveExtensions',
            'postSave'
        ), $document->getEvents());
    }

    public function testDeleteUnique()
    {
        $repository = $this->mondongo->getRepository('Model\Article');
        $articles   = $this->createArticles(10);

        $repository->delete($articles[3]);
        $this->assertSame(9, $this->db->article->find()->count());
        $this->assertSame(0, $this->db->article->find(array('_id' => $articles[3]->getId()))->count());
    }

    public function testDeleteMultiple()
    {
        $repository = $this->mondongo->getRepository('Model\Article');
        $articles   = $this->createArticles(10);

        $repository->delete(array($articles[4], $articles[7]));
        $this->assertSame(8, $this->db->article->find()->count());
        $this->assertSame(0, $this->db->article->find(array(
            '_id' => array('$in' => array($articles[4]->getId(), $articles[7]->getId())),
        ))->count());
    }

    public function testDeleteEvents()
    {
        $repository = $this->mondongo->getRepository('Model\Events');

        $document = new Events();
        $document->setName('Mondongo');
        $repository->save($document);

        $document->clearEvents();
        $repository->delete($document);

        $this->assertSame(array(
            'preDeleteExtensions',
            'preDelete',
            'postDeleteExtensions',
            'postDelete',
        ), $document->getEvents());
    }

    public function testIdentityMapFindCreating()
    {
        $repository = $this->mondongo->getRepository('Model\Article');
        $articles   = $this->createArticles(10);

        $this->assertEquals($articles[1], $repository->query(array('_id' => $articles[1]->getId()))->one());
    }

    public function testIdentityMapFindQuering()
    {
        $repository = $this->mondongo->getRepository('Model\Article');
        $articles   = $this->createArticles(10);

        $repository->getIdentityMap()->clear();

        $this->assertEquals($articles[3], $article = $repository->find($articles[3]->getId()));
        $this->assertNotSame($articles[3], $article);

        $this->assertSame($article, $repository->find($articles[3]->getId()));
    }

    public function testIdentityMapDelete()
    {
        $repository = $this->mondongo->getRepository('Model\Article');
        $articles   = $this->createArticles(10);

        $id = $articles[1]->getId();
        $articles[1]->delete();

        $this->assertFalse($repository->getIdentityMap()->hasById($id));
    }
}
