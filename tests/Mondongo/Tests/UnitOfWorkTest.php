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

use Model\Article;
use Model\Author;
use Model\Category;

class UnitOfWorkTest extends TestCase
{
    public function testPersist()
    {
        $article = new Article();
        $category1 = new Category();
        $category2 = new Category();

        $this->unitOfWork->persist($article);
        $this->unitOfWork->persist($category1);

        $this->assertTrue($this->unitOfWork->isPendingForPersist($article));
        $this->assertTrue($this->unitOfWork->isPendingForPersist($category1));
        $this->assertFalse($this->unitOfWork->isPendingForPersist($category2));
    }

    public function testRemove()
    {
        $article = new Article();
        $article->setTitle('foo');
        $article->save();
        $category1 = new Category();
        $category1->setName('foo');
        $category1->save();
        $category2 = new Category();
        $category2->setName('bar');
        $category2->save();

        $this->unitOfWork->remove($article);
        $this->unitOfWork->remove($category1);

        $this->assertTrue($this->unitOfWork->isPendingForRemove($article));
        $this->assertTrue($this->unitOfWork->isPendingForRemove($category1));
        $this->assertFalse($this->unitOfWork->isPendingForRemove($category2));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveNewDocument()
    {
        $this->unitOfWork->remove(new Article());
    }

    public function testCommit()
    {
        $articleForInsert = new Article();
        $articleForInsert->setTitle('AFI');
        $this->unitOfWork->persist($articleForInsert);

        $articleForUpdate = new Article();
        $articleForUpdate->setTitle('AFI');
        $articleForUpdate->save();
        $articleForUpdate->setTitle('AFU');
        $this->unitOfWork->persist($articleForUpdate);

        $categoriesForInsert = array();
        for ($i = 1; $i <= 5; $i++) {
            $categoriesForInsert[$i] = $category = new Category();
            $category->setName('Category For Insert '.$i);
            $this->unitOfWork->persist($category);
        }

        $categoriesForUpdate = array();
        for ($i = 1; $i <= 5; $i++) {
            $categoriesForUpdate[$i] = $category = new Category();
            $category->setName('Category '.$i);
            $category->save();
            $category->setName('Category For Update '.$i);
            $this->unitOfWork->persist($category);
        }

        $categoriesForRemove = array();
        for ($i = 1; $i <= 5; $i++) {
            $categoriesForRemove[$i] = $category = new Category();
            $category->setName('Category For Remove '.$i);
            $category->save();
            $this->unitOfWork->remove($category);
        }

        $authorForRemove = new Author();
        $authorForRemove->setName('Author For Remove');
        $authorForRemove->save();
        $this->unitOfWork->remove($authorForRemove);

        $this->unitOfWork->commit();

        $this->assertFalse($articleForInsert->isNew());
        $this->assertFalse($articleForInsert->isModified());
        $this->assertFalse($articleForUpdate->isModified());
        foreach ($categoriesForInsert as $category) {
            $this->assertFalse($category->isNew());
            $this->assertFalse($category->isModified());
        }
        foreach ($categoriesForUpdate as $category) {
            $this->assertFalse($category->isModified());
        }
        foreach ($categoriesForRemove as $category) {
            $this->assertNull(Category::collection()->findOne(array('_id' => $category->getId())));
        }
        $this->assertNull(Author::collection()->findOne(array('_id' => $authorForRemove->getId())));
    }

    public function testCommitOnlyReferencedDocuments()
    {
        $author = new Author();
        $author->setName('Pablo');
        $this->unitOfWork->persist($author);

        $article = new Article();
        $article->setAuthor($author);
        $this->unitOfWork->persist($article);

        $this->unitOfWork->commit();

        $this->assertFalse($author->isNew());
        $this->assertFalse($article->isNew());
    }

    public function testHasPendingForPersist()
    {
        $this->assertFalse($this->unitOfWork->hasPendingForPersist());

        $author = new Author();
        $author->setName('Pablo');
        $author->save();

        $this->unitOfWork->remove($author);
        $this->assertFalse($this->unitOfWork->hasPendingForPersist());

        $author = new Author();

        $this->unitOfWork->persist($author);
        $this->assertTrue($this->unitOfWork->hasPendingForPersist());

        $this->unitOfWork->clear();
    }

    public function testHasPendingForRemove()
    {
        $this->assertFalse($this->unitOfWork->hasPendingForRemove());

        $author = new Author();
        $author->setName('Pablo');

        $this->unitOfWork->persist($author);
        $this->assertFalse($this->unitOfWork->hasPendingForRemove());

        $this->unitOfWork->commit();

        $this->unitOfWork->remove($author);
        $this->assertTrue($this->unitOfWork->hasPendingForRemove());

        $this->unitOfWork->clear();
    }

    public function testHasPending()
    {
        $this->assertFalse($this->unitOfWork->hasPending());

        $author = new Author();
        $author->setName('Pablo');

        $this->unitOfWork->persist($author);
        $this->assertTrue($this->unitOfWork->hasPending());

        $this->unitOfWork->commit();

        $this->unitOfWork->remove($author);
        $this->assertTrue($this->unitOfWork->hasPending());
    }

    public function testClear()
    {
        $articleForInsert = new Article();
        $this->unitOfWork->persist($articleForInsert);

        $articleForRemove = new Article();
        $articleForRemove->setTitle('Mondongo');
        $articleForRemove->save();
        $this->unitOfWork->remove($articleForRemove);

        $this->unitOfWork->clear();

        $this->assertFalse($this->unitOfWork->isPendingForPersist($articleForInsert));
        $this->assertFalse($this->unitOfWork->isPendingForRemove($articleForRemove));
    }
}
