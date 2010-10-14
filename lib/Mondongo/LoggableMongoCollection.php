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

namespace Mondongo;

/**
 * A loggable MongoCollection.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class LoggableMongoCollection extends \MongoCollection
{
    protected $loggerCallable;

    /**
     * Set the logger callable.
     *
     * @param mixed $loggerCallable A PHP callable.
     *
     * @return void
     */
    public function setLoggerCallable($loggerCallable)
    {
        $this->loggerCallable = $loggerCallable;
    }

    /**
     * Returns the logger callable.
     *
     * @return mixed The logger callable.
     */
    public function getLoggerCallable()
    {
        return $this->loggerCallable;
    }

    /*
     * log.
     */
    protected function log(array $log)
    {
        if ($this->loggerCallable) {
            call_user_func($this->loggerCallable, array_merge(array(
                'database'   => $this->db->__toString(),
                'collection' => $this->getName(),
            ), $log));
        }
    }

    /*
     * batchInsert.
     */
    public function batchInsert(array $a, array $options = array())
    {
        $this->log(array(
            'batchInsert' => 1,
            'nb'          => count($a),
            'data'        => $a,
            'options'     => $options,
        ));

        return parent::batchInsert($a, $options);
    }

    /*
     * count.
     */
    public function count(array $query = array(), $limit = 0, $skip = 0)
    {
        $this->log(array(
            'count' => 1,
            'query' => $query,
            'limit' => $limit,
            'skip'  => $skip,
        ));

        return parent::count($query, $limit, $skip);
    }

    /*
     * find.
     */
    public function find(array $query = array(), array $fields = array())
    {
        $this->log(array(
            'find'   => 1,
            'query'  => $query,
            'fields' => $fields,
        ));

        return parent::find($query, $fields);
    }

    /*
     * findOne.
     */
    public function findOne(array $query = array(), array $fields = array())
    {
        $this->log(array(
            'findOne' => 1,
            'query'   => $query,
            'fields'  => $fields,
        ));

        return parent::findOne($query, $fields);
    }

    /*
     * insert.
     */
    public function insert(array $a, array $options = array())
    {
        $this->log(array(
            'insert'  => 1,
            'a'       => $a,
            'options' => $options,
        ));

        return parent::insert($a, $options);
    }

    /*
     * remove.
     */
    public function remove(array $criteria = array(), array $options = array())
    {
        $this->log(array(
            'remove'   => 1,
            'criteria' => $criteria,
            'options'  => $options,
        ));

        return parent::remove($criteria, $options);
    }
}
