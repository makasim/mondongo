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

namespace Mondongo\Tests\Extension;

use Model\Article;

class DocumentDataMapTest extends \PHPUnit_Framework_TestCase
{
    public function testDocumentDataMap()
    {
        $this->assertSame(array(
            'fields' => array(
                'title'        => array('type' => 'string'),
                'slug'         => array('type' => 'string'),
                'content'      => array('type' => 'string'),
                'is_active'    => array('type' => 'boolean'),
            ),
            'references_one' => array(
                'author'     => array('class' => 'Model\Author', 'field' => 'author_id'),
            ),
            'references_many' => array(
                'categories' => array('class' => 'Model\Category', 'field' => 'category_ids'),
            ),
            'embeddeds_one' => array(
                'source'   => array('class' => 'Model\Source'),
            ),
            'embeddeds_many' => array(
                'comments' => array('class' => 'Model\Comment'),
            ),
            'relations_one' => array(
                'summary' => array('class' => 'Model\Summary', 'field' => 'article_id'),
            ),
            'relations_many_one' => array(
                'news' => array('class' => 'Model\News', 'field' => 'article_id'),
            ),
            'relations_many_many' => array(
            ),
            'relations_many_through' => array(
                'votes_users' => array('class' => 'Model\User', 'through' => 'Model\ArticleVote', 'local' => 'article_id', 'foreign' => 'user_id')
            ),
        ), Article::getDataMap());
    }
}
