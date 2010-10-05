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

use Mondongo\Tests\PHPUnit\TestCase;
use Mondongo\Connection;
use Mondongo\Mondongo;
use Mondongo\Repository as RepositoryBase;
use Model\Document\Article;

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
        $mondongo   = new Mondongo();
        $repository = new Repository($mondongo);

        $this->assertSame($mondongo, $repository->getMondongo());
    }

    public function testGetDocumentClass()
    {
        $repository = new Repository(new Mondongo());

        $this->assertSame('User', $repository->getDocumentClass());
    }

    public function testGetConnectionName()
    {
        $repository = new Repository(new Mondongo());

        $this->assertSame('default', $repository->getConnectionName());
    }

    public function testGetCollectionName()
    {
        $repository = new Repository(new Mondongo());

        $this->assertSame('users', $repository->getCollectionName());
    }

    public function testGetConnection()
    {
        $mondongo = new Mondongo();
        $mondongo->setConnections(array(
            'local'  => $local  = new Connection('localhost', 'mondongo_tests_local'),
            'global' => $global = new Connection('localhost', 'mondongo_tests_global'),
        ));

        $this->assertSame($local, $mondongo->getRepository('Model\Document\Article')->getConnection());
        $this->assertSame($global, $mondongo->getRepository('Model\Document\ConnectionGlobal')->getConnection());
    }

    public function testFind()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $this->assertEquals($articles, $repository->find());

        $this->assertNull($repository->find(array('query' => array('_id' => new \MongoId('123')))));
    }

    public function testFindOptionQuery()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $this->assertEquals(array($articles[0], $articles[4]), $repository->find(array('query' => array(
            '_id' => array('$in' => array($articles[0]->getId(), $articles[4]->getId()))
        ))));
    }

    public function testFindOptionFields()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $results = $repository->find(array('fields' => array('content' => 1)));

        $this->assertNull($results[0]->getTitle());
        $this->assertNull($results[0]->getIsActive());
        $this->assertNull($results[3]->getTitle());
        $this->assertNull($results[3]->getIsActive());
    }

    public function testFindOptionSort()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $results = $repository->find(array('sort' => array('title' => -1)));

        $this->assertSame('Article 9', $results[0]->getTitle());
        $this->assertSame('Article 8', $results[1]->getTitle());
        $this->assertSame('Article 10', $results[8]->getTitle());
        $this->assertSame('Article 1', $results[9]->getTitle());
    }

    public function testFindOptionLimit()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $this->assertSame(4, count($repository->find(array('limit' => 4))));
        $this->assertSame(6, count($repository->find(array('limit' => 6))));
    }

    public function testFindOptionSkip()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $this->assertEquals(array($articles[8], $articles[9]), $repository->find(array(
            'skip' => 8,
        )));
        $this->assertEquals(array($articles[6], $articles[7], $articles[8], $articles[9]), $repository->find(array(
            'skip' => 6,
        )));
    }

    public function testFindOptionOne()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $this->assertEquals($articles[0], $repository->find(array('query' => array('_id' => $articles[0]->getId()), 'one' => true)));
        $this->assertEquals($articles[4], $repository->find(array('query' => array('_id' => $articles[4]->getId()), 'one' => true)));
    }

    public function testFindOptions()
    {
        // TODO
    }

    public function testFindOne()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $this->assertEquals($articles[0], $repository->findOne());
        $this->assertEquals($articles[3], $repository->findOne(array('query' => array('_id' => $articles[3]->getId()))));

        $this->assertNull($repository->findOne(array('query' => array('_id' => new \MongoId('123')))));
    }

    public function testGet()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $this->assertEquals($articles[2], $repository->get($articles[2]->getId()));
        $this->assertEquals($articles[2], $repository->get($articles[2]->getId()->__toString()));
        $this->assertEquals($articles[5], $repository->get($articles[5]->getId()));

        $this->assertNull($repository->get('123'));
    }

    public function testRemove()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $repository->remove();

        $this->assertSame(0, $this->db->article->find()->count());
    }

    public function testRemoveQuery()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $articles[3]->setTitle('No');
        $articles[3]->save();

        $repository->remove(array('title' => new \MongoRegex('/^Article/')));

        $this->assertSame(1, $this->db->article->find()->count());
        $this->assertSame(1, $this->db->article->find(array('_id' => $articles[3]->getId()))->count());
    }

    public function testCount()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $this->assertSame(10, $repository->count());
    }

    public function testCountQuery()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        for ($i = 1; $i <= 5; $i++) {
            $articles[$i]->setTitle('Count');
            $repository->save($articles[$i]);
        }

        $this->assertSame(5, $repository->count(array('title' => 'Count')));
    }

    public function testSaveInsertUnique()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');

        // insert
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
        $repository = $this->mondongo->getRepository('Model\Document\Article');
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

    public function testRemoveUnique()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $repository->delete($articles[3]);
        $this->assertSame(9, $this->db->article->find()->count());
        $this->assertSame(0, $this->db->article->find(array('_id' => $articles[3]->getId()))->count());
    }

    public function testRemoveMultiple()
    {
        $repository = $this->mondongo->getRepository('Model\Document\Article');
        $articles   = $this->createArticles(10);

        $repository->delete(array($articles[4], $articles[7]));
        $this->assertSame(8, $this->db->article->find()->count());
        $this->assertSame(0, $this->db->article->find(array(
            '_id' => array('$in' => array($articles[4]->getId(), $articles[7]->getId())),
        ))->count());
    }
}