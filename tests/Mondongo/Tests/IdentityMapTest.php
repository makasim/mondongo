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

use Mondongo\IdentityMap;

class IdentityMapTest extends TestCase
{
    public function testAllAndAdd()
    {
        $articles = $this->createArticles(10);

        $identityMap = new IdentityMap();
        $identityMap->add($articles[1]);
        $identityMap->add($articles[5]);

        $this->assertSame(array(
            $articles[1]->getId()->__toString() => $articles[1],
            $articles[5]->getId()->__toString() => $articles[5]
        ), $identityMap->all());
    }

    public function testHasByIdAndHas()
    {
        $articles = $this->createArticles(10);

        $identityMap = new IdentityMap();
        $identityMap->add($articles[1]);
        $identityMap->add($articles[5]);

        $this->assertTrue($identityMap->hasById($articles[1]->getId()));
        $this->assertTrue($identityMap->hasById($articles[5]->getId()));
        $this->assertFalse($identityMap->hasById($articles[2]->getId()));
        $this->assertFalse($identityMap->hasById($articles[3]->getId()));
        $this->assertFalse($identityMap->hasById($articles[4]->getId()));

        $this->assertTrue($identityMap->has($articles[1]));
        $this->assertTrue($identityMap->has($articles[5]));
        $this->assertFalse($identityMap->has($articles[2]));
        $this->assertFalse($identityMap->has($articles[3]));
        $this->assertFalse($identityMap->has($articles[4]));
    }

    public function testGetById()
    {
        $articles = $this->createArticles(10);

        $identityMap = new IdentityMap();
        $identityMap->add($articles[1]);
        $identityMap->add($articles[5]);

        $this->assertSame($articles[1], $identityMap->getById($articles[1]->getId()));
        $this->assertSame($articles[5], $identityMap->getById($articles[5]->getId()));
    }

    public function testRemoveById()
    {
        $articles = $this->createArticles(10);

        $identityMap = new IdentityMap();
        $identityMap->add($articles[1]);
        $identityMap->add($articles[5]);

        $identityMap->removeById($articles[1]->getId());

        $this->assertFalse($identityMap->has($articles[1]));
        $this->assertTrue($identityMap->has($articles[5]));
    }

    public function testRemove()
    {
        $articles = $this->createArticles(10);

        $identityMap = new IdentityMap();
        $identityMap->add($articles[1]);
        $identityMap->add($articles[5]);

        $identityMap->remove($articles[1]);

        $this->assertFalse($identityMap->has($articles[1]));
        $this->assertTrue($identityMap->has($articles[5]));
    }

    public function testClear()
    {
        $articles = $this->createArticles(10);

        $identityMap = new IdentityMap();
        $identityMap->add($articles[1]);
        $identityMap->add($articles[5]);

        $identityMap->clear();

        $this->assertSame(array(), $identityMap->all());
    }
}
