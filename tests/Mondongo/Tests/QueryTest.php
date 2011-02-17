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

use Mondongo\Query;

class QueryTest extends TestCase
{
    public function testConstruct()
    {
        $repository = \Model\Article::repository();
        $query = new Query($repository);
        $this->assertSame($repository, $query->getRepository());
    }

    public function testCriteria()
    {
        $query = new Query(\Model\Article::repository());
        $this->assertSame(array(), $query->getCriteria());

        $criteria = array('is_active' => true);
        $this->assertSame($query, $query->criteria($criteria));
        $this->assertSame($criteria, $query->getCriteria());

        $criteria = array('title' => 'foo', 'content' => 'bar');
        $query->criteria($criteria);
        $this->assertSame($criteria, $query->getCriteria());
    }

    public function testFields()
    {
        $query = new Query(\Model\Article::repository());
        $this->assertSame(array(), $query->getFields());

        $fields = array('title' => 1, 'content' => 1);
        $this->assertSame($query, $query->fields($fields));
        $this->assertSame($fields, $query->getFields());

        $fields = array('_id' => 1);
        $query->fields($fields);
        $this->assertSame($fields, $query->getFields());
    }

    public function testSort()
    {
        $query = new Query(\Model\Article::repository());
        $this->assertNull($query->getSort());

        $sort = array('is_active' => 1);
        $this->assertSame($query, $query->sort($sort));
        $this->assertSame($sort, $query->getSort());

        $sort = array('date' => -1, 'title' => 1);
        $query->sort($sort);
        $this->assertSame($sort, $query->getSort());

        $query->sort(null);
        $this->assertNull($query->getSort());
    }

    public function testLimit()
    {
        $query = new Query(\Model\Article::repository());
        $this->assertNull($query->getLimit());

        $this->assertSame($query, $query->limit(10));
        $this->assertSame(10, $query->getLimit());

        $query->limit('20');
        $this->assertSame(20, $query->getLimit());

        $query->limit(null);
        $this->assertNull($query->getLimit());
    }

    public function testSkip()
    {
        $query = new Query(\Model\Article::repository());
        $this->assertNull($query->getSkip());

        $this->assertSame($query, $query->skip(15));
        $this->assertSame(15, $query->getSkip());

        $query->skip('40');
        $this->assertSame(40, $query->getSkip());

        $query->skip(null);
        $this->assertNull($query->getSkip());
    }

    public function testGetIterator()
    {
        $query = new Query(\Model\Article::repository());
        $articles = $this->createArticles(10);

        $this->assertEquals($articles, iterator_to_array($query));
    }

    public function testGetIteratorGridFS()
    {
        $file = __DIR__.'/MondongoTest.php';

        $repository = \Model\Image::repository();

        $image = new \Model\Image();
        $image->setFile($file);
        $image->setName('Mondongo');
        $image->setDescription('Foobar');
        $repository->save($image);

        $repository->getIdentityMap()->clear();

        $query = new Query($repository);
        $image = $query->one();
        $result = $this->connection->getMongoDB()->getGridFS('image')->findOne();

        $this->assertEquals($result, $image->getFile());
        $this->assertSame('Mondongo', $image->getName());
        $this->assertSame('Foobar', $image->getDescription());
    }

    public function testAll()
    {
        $query = new Query(\Model\Article::repository());
        $articles = $this->createArticles(10);

        $this->assertEquals($articles, $query->all());
    }

    public function testOne()
    {
        $query = new Query(\Model\Article::repository());
        $articles = $this->createArticles(10);

        $this->assertEquals($articles[0], $query->one());
    }

    public function testOneWithoutResults()
    {
        $query = new Query(\Model\Article::repository());
        $this->assertNull($query->one());
    }

    public function testOneNotChangeQueryLimit()
    {
        $query = new Query(\Model\Article::repository());
        $query->limit(10);
        $query->one();
        $this->assertSame(10, $query->getLimit());
    }

    public function testCount()
    {
        $query = new Query(\Model\Article::repository());

        $articles = $this->createRawArticles(20);
        $this->assertSame(20, $query->count());
    }

    public function testCountableInterface()
    {
        $query = new Query(\Model\Article::repository());

        $articles = $this->createRawArticles(5);
        $this->assertSame(5, count($query));
    }

    public function testCreateCursor()
    {
        $query = new Query(\Model\Article::repository());

        $cursor = $query->createCursor();
        $this->assertInstanceOf('MongoCursor', $cursor);

        $articles = $this->createRawArticles(10);
        $results = iterator_to_array($cursor);
        foreach ($articles as $article) {
            $this->assertTrue(isset($results[$article['_id']->__toString()]));
        }
    }
}
