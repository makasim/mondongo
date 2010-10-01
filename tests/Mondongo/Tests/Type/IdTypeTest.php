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

namespace Mondongo\Tests\Type;

use Mondongo\Tests\PHPUnit\TestCase;
use Mondongo\Type\IdType;

class IdTypeTest extends TestCase
{
    public function testToMongo()
    {
        $type = new IdType();
        $this->assertEquals(new \MongoId('123'), $type->toMongo('123'));
    }

    public function testToPHP()
    {
        $type = new IdType();
        $id   = new \MongoId('123');
        $this->assertSame((string) $id, $type->toPHP($id));
    }

    public function testToMongoInString()
    {
        $type = new IdType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $this->assertEquals(new \MongoId('123'), $type->toMongo('123'));
    }

    public function testToPHPInString()
    {
        $type = new IdType();
        $id   = new \MongoId('123');
        $function = $this->getTypeFunction($type->toPHPInString());

        $this->assertSame((string) $id, $type->toPHP($id));
    }
}
