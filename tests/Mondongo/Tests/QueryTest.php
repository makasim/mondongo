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
    public function testConstructGetRepository()
    {
        $repository = \Model\Article::repository();
        $query = new Query($repository);
        $this->assertSame($repository, $query->getRepository());
    }

    public function testGetCursor()
    {
        $this->createArticles(1);

        $query = new Query(\Model\Article::repository());
        $this->assertNull($query->getCursor());
        foreach ($query as $article) {
            $this->assertInstanceOf('MongoCursor', $query->getCursor());
        }
        $this->assertNull($query->getCursor());

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

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotArrayOrNull
     */
    public function testCriteriaNotArrayOrNull($value)
    {
        $query = new Query(\Model\Article::repository());
        $query->criteria($value);
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

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotArrayOrNull
     */
    public function testFieldsNotArrayOrNull($value)
    {
        $query = new Query(\Model\Article::repository());
        $query->fields($value);
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

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotArrayOrNull
     */
    public function testSortNotArrayOrNull($value)
    {
        $query = new Query(\Model\Article::repository());
        $query->sort($value);
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

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotValidIntOrNull
     */
    public function testLimitNotValidIntOrNull($value)
    {
        $query = new Query(\Model\Article::repository());
        $query->limit($value);
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

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotValidIntOrNull
     */
    public function testSkipNotValidIntOrNull($value)
    {
        $query = new Query(\Model\Article::repository());
        $query->skip($value);
    }

    public function testBatchSize()
    {
        $query = new Query(\Model\Article::repository());
        $this->assertNull($query->getBatchSize());

        $this->assertSame($query, $query->batchSize(15));
        $this->assertSame(15, $query->getBatchSize());

        $query->batchSize('40');
        $this->assertSame(40, $query->getBatchSize());

        $query->batchSize(null);
        $this->assertNull($query->getBatchSize());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotValidIntOrNull
     */
    public function testBatchSizeNotValidIntOrNull($value)
    {
        $query = new Query(\Model\Article::repository());
        $query->batchSize($value);
    }

    public function testHint()
    {
        $query = new Query(\Model\Article::repository());
        $this->assertNull($query->getHint());

        $hint = array('username' => 1);
        $this->assertSame($query, $query->hint($hint));
        $this->assertSame($hint, $query->getHint());

        $hint = array('username' => 1, 'date' => 1);
        $query->hint($hint);
        $this->assertSame($hint, $query->getHint());

        $query->hint(null);
        $this->assertNull($query->getHint());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotArrayOrNull
     */
    public function testHintNotArrayOrNull($value)
    {
        $query = new Query(\Model\Article::repository());
        $query->hint($value);
    }

    public function testSnapshot()
    {
        $query = new Query(\Model\Article::repository());
        $this->assertFalse($query->getSnapshot());

        $this->assertSame($query, $query->snapshot(true));
        $this->assertTrue($query->getSnapshot());

        $query->snapshot(false);
        $this->assertFalse($query->getSnapshot());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotBoolean
     */
    public function testSnapshotNotBoolean($value)
    {
        $query = new Query(\Model\Article::repository());
        $query->snapshot($value);
    }

    public function testTailable()
    {
        $query = new Query(\Model\Article::repository());
        $this->assertFalse($query->getTailable());

        $this->assertSame($query, $query->tailable(true));
        $this->assertTrue($query->getTailable());

        $query->tailable(false);
        $this->assertFalse($query->getTailable());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotBoolean
     */
    public function testTailableNotBoolean($value)
    {
        $query = new Query(\Model\Article::repository());
        $query->tailable($value);
    }

    public function testTimeout()
    {
        $query = new Query(\Model\Article::repository());
        $this->assertNull($query->getTimeout());

        $this->assertSame($query, $query->timeout(15));
        $this->assertSame(15, $query->getTimeout());

        $query->timeout('40');
        $this->assertSame(40, $query->getTimeout());

        $query->timeout(null);
        $this->assertNull($query->getTimeout());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotValidIntOrNull
     */
    public function testTimeoutNotValidIntOrNull($value)
    {
        $query = new Query(\Model\Article::repository());
        $query->timeout($value);
    }

    public function testIterator()
    {
        $query = new Query(\Model\Article::repository());
        $articles = $this->createArticles(10);

        $results = iterator_to_array($query);
        foreach ($articles as $article) {
            $this->assertTrue(in_array($article, $results));
        }
    }

    public function testIteratorGridFS()
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

        $results = $query->all();
        foreach ($articles as $article) {
            $this->assertTrue(in_array($article, $results));
        }
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

    public function testCreateCursorPlaying()
    {
        $query = new Query(\Model\Article::repository());

        $query
            ->criteria(array('is_active' => true))
            ->fields(array('title' => 1))
            ->sort(array('date' => -1))
            ->limit(10)
            ->skip(25)
            ->batchSize(5)
            ->hint(array('username' => 1))
            ->snapshot(true)
            ->tailable(true)
            ->timeout(100)
        ;

        $cursor = $query->createCursor();
        $this->assertInstanceOf('MongoCursor', $cursor);
    }

    public function providerNotArrayOrNull()
    {
        return array(
            array(true),
            array(1),
            array('string'),
        );
    }

    public function providerNotValidIntOrNull()
    {
        return array(
            array(true),
            array(array(1, 2)),
            array(1.1),
        );
    }

    public function providerNotBoolean()
    {
        return array(
            array(1),
            array('true'),
            array(array(true)),
        );
    }
}
