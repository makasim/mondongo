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
            $this->assertNull($category->getCollection()->findOne(array('_id' => $category->getId())));
        }
        $this->assertNull($authorForRemove->getCollection()->findOne(array('_id' => $authorForRemove->getId())));
    }
}
