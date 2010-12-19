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

use Mondongo\DataLoader;
use Mondongo\Mondongo;
use Model\Author;

class DataLoaderTest extends TestCase
{
    public function testConstructor()
    {
        $dataLoader = new DataLoader($mondongo = new Mondongo(), $datum = array('foo' => 'bar'));

        $this->assertSame($mondongo, $dataLoader->getMondongo());
        $this->assertSame($datum, $dataLoader->getData());
    }

    public function testSetGetMondongo()
    {
        $dataLoader = new DataLoader(new Mondongo());
        $dataLoader->setMondongo($mondongo = new Mondongo());

        $this->assertSame($mondongo, $dataLoader->getMondongo());
    }

    public function testSetGetData()
    {
        $dataLoader = new DataLoader(new Mondongo());
        $dataLoader->setData($datum = array('foo' => 'bar', 'bar' => 'foo'));

        $this->assertSame($datum, $dataLoader->getData());
    }

    public function testLoad()
    {
        $dataLoader = new DataLoader($this->mondongo, array(
            'Model\Article' => array(
                'article_1' => array(
                    'title'   => 'Article 1',
                    'content' => 'Contuent',
                    'author'  => 'sormes',
                    'categories' => array(
                        'category_2',
                        'category_3',
                    ),
                ),
                'article_2' => array(
                    'title' => 'My Article 2',
                ),
            ),
            'Model\Author' => array(
                'pablodip' => array(
                    'name' => 'PabloDip',
                ),
                'sormes' => array(
                    'name' => 'Francisco',
                ),
                'barbelith' => array(
                    'name' => 'Pedro',
                ),
            ),
            'Model\Category' => array(
                'category_1' => array(
                    'name' => 'Category1',
                ),
                'category_2' => array(
                    'name' => 'Category2',
                ),
                'category_3' => array(
                    'name' => 'Category3',
                ),
                'category_4' => array(
                    'name' => 'Category4',
                ),
            ),
        ));
        $dataLoader->load(true);

        $articleRepository = $this->mondongo->getRepository('Model\Article');
        $authorRepository  = $this->mondongo->getRepository('Model\Author');
        $categoryRepository  = $this->mondongo->getRepository('Model\Category');

        // articles
        $this->assertSame(2, $articleRepository->count());

        $article = $articleRepository->findOne(array('query' => array('title' => 'Article 1')));
        $this->assertNotNull($article);
        $this->assertSame('Contuent', $article->getContent());
        $this->assertSame('Francisco', $article->getAuthor()->getName());
        $this->assertSame(2, $article->getCategories()->count());

        $article = $articleRepository->findOne(array('query' => array('title' => 'My Article 2')));
        $this->assertNotNull($article);
        $this->assertNull($article->getAuthorId());

        // authors
        $this->assertSame(3, $authorRepository->count());

        $author = $authorRepository->findOne(array('query' => array('name' => 'PabloDip')));
        $this->assertNotNull($author);

        $author = $authorRepository->findOne(array('query' => array('name' => 'Francisco')));
        $this->assertNotNull($author);

        $author = $authorRepository->findOne(array('query' => array('name' => 'Pedro')));
        $this->assertNotNull($author);

        // categories
        $this->assertSame(4, $categoryRepository->count());
    }

    public function testLoadPrune()
    {
        foreach ($this->mondongo->getConnections() as $connection) {
            $connection->getMongoDB()->drop();
        }

        $dataLoader = new DataLoader($this->mondongo, array(
            'Model\Author' => array(
                'pablodip' => array(
                    'name' => 'Pablo',
                ),
            ),
        ));

        $authorRepository = $this->mondongo->getRepository('Model\Author');

        $dataLoader->load();
        $this->assertSame(1, $authorRepository->count());

        $dataLoader->load();
        $this->assertSame(2, $authorRepository->count());

        $dataLoader->load(false);
        $this->assertSame(3, $authorRepository->count());

        $dataLoader->load(true);
        $this->assertSame(1, $authorRepository->count());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLoadMondongoUnitOfWorkHasPending()
    {
        $author = new Author();
        $author->setName('Pablo');
        $this->mondongo->persist($author);

        $dataLoader = new DataLoader($this->mondongo);
        $dataLoader->load();
    }
}
