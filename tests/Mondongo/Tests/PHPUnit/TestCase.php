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

namespace Mondongo\Tests\PHPUnit;

use Mondongo\Container;
use Mondongo\Connection;
use Mondongo\Mondongo;
use Mondongo\Type\Container as TypeContainer;
use Model\Document\Article;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $mongo;

    protected $db;

    protected $mondongo;

    public function setUp()
    {
        Container::clearDefault();
        Container::clearForDocumentClasses();

        TypeContainer::resetTypes();

        $this->mongo = new \Mongo();

        $this->mongo->dropDB('mondongo_tests');
        $this->db = $this->mongo->selectDB('mondongo_tests');

        $this->mondongo = new Mondongo();
        $this->mondongo->setConnection('default', new Connection('localhost', 'mondongo_tests'));

        Container::setDefault($this->mondongo);
    }

    protected function createArticles($nb)
    {
        $articles = array();
        for ($i = 1; $i <= $nb; $i++) {
            $articles[] = $a = new Article();
            $a->setTitle('Article '.$i);
            $a->setContent('Content');
        }
        $this->mondongo->getRepository('Model\Document\Article')->save($articles);

        return $articles;
    }

    protected function getTypeFunction($string)
    {
        eval('$function = function($from) { '.strtr($string, array(
            '%from%' => '$from',
            '%to%'   => '$to',
        )).' return $to; };');

        return $function;
    }
}
