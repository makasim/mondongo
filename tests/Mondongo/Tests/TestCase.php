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

use Mondongo\Container;
use Mondongo\Connection;
use Mondongo\Mondongo;
use Mondongo\Type\Container as TypeContainer;
use Model\Article;

class TestCase extends \PHPUnit_Framework_TestCase
{
    static protected $sConnection;
    static protected $sMondongo;

    protected $metadataClass = 'Model\Info\Metadata';
    protected $server = 'mongodb://localhost';
    protected $dbName = 'mondongo_tests';
    protected $dbNameGlobal = 'mondongo_tests_global';
    protected $connection;
    protected $mondongo;
    protected $unitOfWork;
    protected $metadata;
    protected $mongo;
    protected $db;

    protected function setUp()
    {
        Container::clear();

        if (!static::$sConnection) {
            static::$sConnection = new Connection($this->server, $this->dbName);
        }
        $this->connection = static::$sConnection;

        if (!static::$sMondongo) {
            static::$sMondongo = new Mondongo(new \Model\Info\Metadata(), function($log) {});
            static::$sMondongo->setConnection('default', $this->connection);
            static::$sMondongo->setConnection('global', new Connection($this->server, $this->dbNameGlobal));
            static::$sMondongo->setDefaultConnectionName('default');
        }
        $this->mondongo = static::$sMondongo;
        $this->unitOfWork = $this->mondongo->getUnitOfWork();
        $this->metadata = $this->mondongo->getMetadata();

        $this->mongo = $this->connection->getMongo();
        $this->db = $this->connection->getMongoDB();

        foreach ($this->db->listCollections() as $collection) {
            $collection->drop();
        }

        Container::set('default', $this->mondongo);
        Container::setDefaultName('default');
    }

    protected function createArticles($nb)
    {
        $articles = array();
        for ($i = 0; $i < $nb; $i++) {
            $articles[] = $a = new Article();
            $a->setTitle('Article '.$i);
            $a->setContent('Content');
        }
        $this->mondongo->getRepository('Model\Article')->save($articles);

        return $articles;
    }
}
