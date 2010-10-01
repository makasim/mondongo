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

use Mondongo\Tests\PHPUnit\TestCase;
use Mondongo\Connection;
use Mondongo\Mondongo;

class MondongoTest extends TestCase
{
    public function testConnections()
    {
        $connections = array(
            'local'  => new Connection('localhost', 'mondongo_tests_local'),
            'global' => new Connection('localhost', 'mondongo_tests_global'),
            'extra'  => new Connection('localhost', 'mondongo_tests_extra'),
        );

        // hasConnection, setConnection, getConnection
        $mondongo = new Mondongo();
        $this->assertFalse($mondongo->hasConnection('local'));
        $mondongo->setConnection('local', $connections['local']);
        $this->assertTrue($mondongo->hasConnection('local'));
        $mondongo->setConnection('extra', $connections['extra']);
        $this->assertSame($connections['local'], $mondongo->getConnection('local'));
        $this->assertSame($connections['extra'], $mondongo->getConnection('extra'));

        // setConnections, getConnections
        $mondongo = new Mondongo();
        $mondongo->setConnection('extra', $connections['extra']);
        $mondongo->setConnections($setConnections = array(
          'local'  => $connections['local'],
          'global' => $connections['global'],
        ));
        $this->assertEquals($setConnections, $mondongo->getConnections());

        // removeConnection
        $mondongo = new Mondongo();
        $mondongo->setConnections($connections);
        $mondongo->removeConnection('local');
        $this->assertSame(array(
          'global' => $connections['global'],
          'extra'  => $connections['extra'],
        ), $mondongo->getConnections());

        // clearConnections
        $mondongo = new Mondongo();
        $mondongo->setConnections($connections);
        $mondongo->clearConnections();
        $this->assertSame(array(), $mondongo->getConnections());

        // defaultConnection
        $mondongo = new Mondongo();
        $mondongo->setConnections($connections);
        $mondongo->setDefaultConnectionName('global');
        $this->assertSame($connections['global'], $mondongo->getDefaultConnection());
        $mondongo->setDefaultConnectionName(null);
        $this->assertSame($connections['local'], $mondongo->getDefaultConnection());
    }

    public function testDefaultConnectionName()
    {
        $mondongo = new Mondongo();
        $this->assertNull($mondongo->getDefaultConnectionName());
        $mondongo->setDefaultConnectionName('mondongo_connection');
        $this->assertSame('mondongo_connection', $mondongo->getDefaultConnectionName());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveConnectionNotExists()
    {
        $mondongo = new Mondongo();
        $mondongo->removeConnection('no');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetConnectionNotExists()
    {
        $mondongo = new Mondongo();
        $mondongo->getConnection('no');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetDefaultConnectionNotExists()
    {
        $mondongo = new Mondongo();
        $mondongo->setDefaultConnectionName('local');
        $mondongo->getDefaultConnection();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testgetDefaultConnectionThereIsNotConnections()
    {
        $mondongo = new Mondongo();
        $mondongo->getDefaultConnection();
    }

    public function testGetRepository()
    {
        $mondongo = new Mondongo();

        $articleRepository = $mondongo->getRepository('Model\Document\Article');
        $this->assertInstanceOf('Model\Repository\Article', $articleRepository);
        $this->assertSame($mondongo, $articleRepository->getMondongo());
        $this->assertSame($articleRepository, $mondongo->getRepository('Model\Document\Article'));

        $userRepository = $mondongo->getRepository('Model\Document\User');
        $this->assertInstanceOf('Model\Repository\User', $userRepository);
    }
}
