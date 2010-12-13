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

namespace Mondongo\Tests\Logger;

use Mondongo\Tests\TestCase;
use Mondongo\Logger\LoggableMongo;
use Mondongo\Logger\LoggableMongoGridFS;

class LoggableMongoGridFSTest extends TestCase
{
    protected $log;

    public function testConstructorAndGetDB()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mondongo_logger');

        $grid = new LoggableMongoGridFS($db, 'mondongo_logger_grid');

        $this->assertSame('mondongo_logger_grid.files', $grid->getName());
        $this->assertSame($db, $grid->getDB());
    }

    public function testLog()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));
        $db = $mongo->selectDB('mondongo_logger');
        $grid = $db->getGridFS('mondongo_logger_grid');

        $grid->log($log = array('foo' => 'bar'));

        $this->assertSame(array_merge(array(
            'database'   => 'mondongo_logger',
            'collection' => 'mondongo_logger_grid.files',
            'gridfs'     => 1
        ), $log), $this->log);
    }

    public function log(array $log)
    {
        $this->log = $log;
    }

    public function testFind()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));
        $db = $mongo->selectDB('mondongo_logger');
        $grid = $db->getGridFS('mondongo_logger_grid');

        $cursor = $grid->find();
        $this->assertInstanceOf('\Mondongo\Logger\LoggableMongoGridFSCursor', $cursor);

        $cursor = $grid->find($query = array('foo' => 'bar'), $fields = array('foobar' => 1, 'barfoo' => 1));
        $info = $cursor->info();
        $this->assertSame($query, $info['query']);
        $this->assertSame($fields, $info['fields']);
    }
}
