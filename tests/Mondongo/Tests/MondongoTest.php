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
use Model\Article;

class MondongoTest extends TestCase
{
    public function testGetUnitOfWork()
    {
        $mondongo = new Mondongo($this->metadata);

        $this->assertInstanceOf('Mondongo\UnitOfWork', $unitOfWork = $mondongo->getUnitOfWork());
        $this->assertSame($mondongo, $unitOfWork->getMondongo());
        $this->assertSame($unitOfWork, $mondongo->getUnitOfWork());
    }

    public function testGetMetadata()
    {
        $mondongo = new Mondongo($this->metadata);

        $this->assertSame($this->metadata, $mondongo->getMetadata());
    }

    public function testLoggerCallable()
    {
        $loggerCallable = function() {};

        $mondongo = new Mondongo($this->metadata, $loggerCallable);
        $this->assertSame($loggerCallable, $mondongo->getLoggerCallable());
    }

    public function testConnections()
    {
        $connections = array(
            'local'  => new Connection('localhost', 'mondongo_tests_local'),
            'global' => new Connection('localhost', 'mondongo_tests_global'),
            'extra'  => new Connection('localhost', 'mondongo_tests_extra'),
        );

        // hasConnection, setConnection, getConnection
        $mondongo = new Mondongo($this->metadata);
        $this->assertFalse($mondongo->hasConnection('local'));
        $mondongo->setConnection('local', $connections['local']);
        $this->assertTrue($mondongo->hasConnection('local'));
        $mondongo->setConnection('extra', $connections['extra']);
        $this->assertSame($connections['local'], $mondongo->getConnection('local'));
        $this->assertSame($connections['extra'], $mondongo->getConnection('extra'));

        // setConnections, getConnections
        $mondongo = new Mondongo($this->metadata);
        $mondongo->setConnection('extra', $connections['extra']);
        $mondongo->setConnections($setConnections = array(
          'local'  => $connections['local'],
          'global' => $connections['global'],
        ));
        $this->assertEquals($setConnections, $mondongo->getConnections());

        // removeConnection
        $mondongo = new Mondongo($this->metadata);
        $mondongo->setConnections($connections);
        $mondongo->removeConnection('local');
        $this->assertSame(array(
          'global' => $connections['global'],
          'extra'  => $connections['extra'],
        ), $mondongo->getConnections());

        // clearConnections
        $mondongo = new Mondongo($this->metadata);
        $mondongo->setConnections($connections);
        $mondongo->clearConnections();
        $this->assertSame(array(), $mondongo->getConnections());

        // defaultConnection
        $mondongo = new Mondongo($this->metadata);
        $mondongo->setConnections($connections);
        $mondongo->setDefaultConnectionName('global');
        $this->assertSame($connections['global'], $mondongo->getDefaultConnection());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetDefaultConnectionWithoutDefaultConnectionName()
    {
        $mondongo = new Mondongo($this->metadata);
        $mondongo->getDefaultConnection();
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetDefaultConnectionConnectionDoesNotExist()
    {
        $mondongo = new Mondongo($this->metadata);
        $mondongo->setConnection('global', new Connection('localhost', 'mondongo_tests'));
        $mondongo->getDefaultConnection();
    }

    public function testSetConnectionLoggerCallable()
    {
        $mondongo = new Mondongo($this->metadata);
        $connection = new Connection('localhost', 'mondongo_tests');
        $mondongo->setConnection('default', $connection);
        $this->assertNull($connection->getLoggerCallable());
        $this->assertNull($connection->getLogDefault());

        $mondongo = new Mondongo($this->metadata, $loggerCallable = function() {});
        $connection = new Connection('localhost', 'mondongo_tests');
        $mondongo->setConnection('default', $connection);
        $this->assertSame($loggerCallable, $connection->getLoggerCallable());
        $this->assertSame(array('connection' => 'default'), $connection->getLogDefault());
    }

    public function testDefaultConnectionName()
    {
        $mondongo = new Mondongo($this->metadata);
        $this->assertNull($mondongo->getDefaultConnectionName());
        $mondongo->setDefaultConnectionName('mondongo_connection');
        $this->assertSame('mondongo_connection', $mondongo->getDefaultConnectionName());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveConnectionNotExists()
    {
        $mondongo = new Mondongo($this->metadata);
        $mondongo->removeConnection('no');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetConnectionNotExists()
    {
        $mondongo = new Mondongo($this->metadata);
        $mondongo->getConnection('no');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetDefaultConnectionNotExists()
    {
        $mondongo = new Mondongo($this->metadata);
        $mondongo->setDefaultConnectionName('local');
        $mondongo->getDefaultConnection();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testgetDefaultConnectionThereIsNotConnections()
    {
        $mondongo = new Mondongo($this->metadata);
        $mondongo->getDefaultConnection();
    }

    public function testGetRepository()
    {
        $mondongo = new Mondongo($this->metadata);

        $articleRepository = $mondongo->getRepository('Model\Article');
        $this->assertInstanceOf('Model\ArticleRepository', $articleRepository);
        $this->assertSame($mondongo, $articleRepository->getMondongo());
        $this->assertSame($articleRepository, $mondongo->getRepository('Model\Article'));

        $userRepository = $mondongo->getRepository('Model\User');
        $this->assertInstanceOf('Model\UserRepository', $userRepository);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetRepositoryNotValidClassEmbeddedDocument()
    {
        $this->mondongo->getRepository('Model\Source');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetRepositoryNotValidClassOtherClass()
    {
        $this->mondongo->getRepository('Article');
    }

    public function testGetAllRepositories()
    {
        $repositories = $this->mondongo->getAllRepositories();

        $this->assertTrue(is_array($repositories));
        $this->assertSame(count($this->metadata->getDocumentClasses()), count($repositories));
    }

    public function testEnsureAllIndexes()
    {
        $this->mondongo->ensureAllIndexes();
    }

    public function testFind()
    {
        $articles = $this->createArticles(10);

        $this->assertEquals($articles, $this->mondongo->find('Model\Article'));
    }

    public function testFindOptions()
    {
        $articles = $this->createArticles(10);

        $this->assertEquals($articles[3], $this->mondongo->find('Model\Article', array('_id' => $articles[3]->getId()), array('one' => true)));
    }

    public function testFindOne()
    {
        $articles = $this->createArticles(10);

        $this->assertEquals($articles[0], $this->mondongo->findOne('Model\Article'));

        $this->assertEquals($articles[3], $this->mondongo->findOne('Model\Article', array('_id' => $articles[3]->getId())));
    }

    public function testFindOneById()
    {
        $articles = $this->createArticles(10);

        $this->assertEquals($articles[3], $this->mondongo->findOneById('Model\Article', $articles[3]->getId()));
    }

    public function testCount()
    {
        $articles = $this->createArticles(10);

        $this->assertSame(10, $this->mondongo->count('Model\Article'));
    }

    public function testCountQuery()
    {
        $articles = $this->createArticles(10);

        for ($i = 1; $i <= 5; $i++) {
            $articles[$i]->setTitle('Count');
            $articles[$i]->save();
        }

        $this->assertSame(5, $this->mondongo->count('Model\Article', array('title' => 'Count')));
    }

    public function testPersist()
    {
        $article = new Article();
        $this->mondongo->persist($article);

        $this->assertTrue($this->unitOfWork->isPendingForPersist($article));
    }

    public function testRemove()
    {
        $article = new Article();
        $article->setTitle('Mondongo');
        $article->save();
        $this->mondongo->remove($article);

        $this->assertTrue($this->unitOfWork->isPendingForRemove($article));
    }

    public function testFlush()
    {
        $article = new Article();
        $article->setTitle('Mondongo');
        $this->mondongo->persist($article);
        $this->mondongo->flush();

        $this->assertFalse($article->isNew());
    }
}
