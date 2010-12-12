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

namespace Mondongo\Log;

/**
 * A loggable MongoGridFS.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class LoggableMongoGridFS extends \MongoGridFS
{
    protected $mongo;

    protected $loggerCallable;

    protected $connectionName;

    protected $time;

    /**
     * Constructor.
     *
     * @param \Mongo   $mongo          The mongo connection object.
     * @param \MongoDB $db             The mongo database object.
     * @param string   $collectionName The collection name.
     *
     * @return void
     */
    public function __construct(\Mongo $mongo, \MongoDB $db, $collectionName)
    {
        parent::__construct($db, $collectionName);

        $this->mongo = $mongo;

        $this->time = new Time();
    }

    /**
     * Returns the mongo connection object.
     *
     * @return \Mongo The mongo connection object.
     */
    public function getMongo()
    {
        return $this->mongo;
    }

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

    /**
     * Set the connection name (for log).
     *
     * @param string $connectionName The connection name.
     *
     * @return void
     */
    public function setConnectionName($connectionName)
    {
        $this->connectionName = $connectionName;
    }

    /**
     * Returns the connection name.
     *
     * @return string The connection name.
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /*
     * storeBytes.
     */
    public function storeBytes($bytes, array $extra, array $options = array())
    {
        $this->time->start();
        $return = parent::storeBytes($bytes, $extra, $options);
        $time = $this->time->stop();

        $this->log(array(
            'type'       => 'storeBytes',
            'bytes_sha1' => sha1($bytes),
            'extra'      => $extra,
            'options'    => $options,
            'time'       => $time,
        ));

        return $return;
    }

    /*
     * storeFile.
     */
    public function storeFile($filename, array $extra, array $options = array())
    {
        $this->time->start();
        $return = parent::storeFile($filename, $extra, $options);
        $time = $this->time->stop();

        $this->log(array(
            'type'      => 'storeFile',
            'filename'  => $filename,
            'extra'     => $extra,
            'options'   => $options,
            'time'      => $time,
        ));

        return $return;
    }

    /*
     * count.
     */
    public function count(array $query = array(), $limit = 0, $skip = 0)
    {
        $this->time->start();
        $return = parent::count($query, $limit, $skip);
        $time = $this->time->stop();

        $this->log(array(
            'type'  => 'count',
            'query' => $query,
            'limit' => $limit,
            'skip'  => $skip,
            'time'  => $time,
        ));

        return $return;
    }

    /*
     * find.
     */
    public function find(array $query = array(), array $fields = array())
    {
        $cursor = new LoggableMongoGridFSCursor($this, $this->mongo, $this->db->__toString().'.'.$this->getName(), $query, $fields);
        $cursor->setLoggerCallable($this->loggerCallable);
        $cursor->setConnectionName($this->connectionName);

        return $cursor;
    }

    /*
     * findOne.
     */
    public function findOne(array $query = array(), array $fields = array())
    {
        $cursor = new LoggableMongoGridFSCursor($this, $this->mongo, $this->db->__toString().'.'.$this->getName(), $query, $fields, LoggableMongoCursor::TYPE_FIND_ONE);
        $cursor->setLoggerCallable($this->loggerCallable);
        $cursor->setConnectionName($this->connectionName);
        $cursor->limit(-1);

        return $cursor->getNext();
    }

    /*
     * insert.
     */
    public function insert(array $a, array $options = array())
    {
        $this->time->start();
        $return = parent::insert($a, $options);
        $time = $this->time->stop();

        $this->log(array(
            'type'    => 'insert',
            'a'       => $a,
            'options' => $options,
            'time'    => $time,
        ));

        return $return;
    }

    /*
     * remove.
     */
    public function remove(array $criteria = array(), array $options = array())
    {
        $this->time->start();
        $return = parent::remove($criteria, $options);
        $time = $this->time->stop();

        $this->log(array(
            'type'     => 'remove',
            'criteria' => $criteria,
            'options'  => $options,
            'time'     => $time,
        ));

        return $return;
    }

    /*
     * log.
     */
    protected function log(array $log)
    {
        if ($this->loggerCallable) {
            call_user_func($this->loggerCallable, array_merge(array(
                'connection' => $this->connectionName,
                'database'   => $this->db->__toString(),
                'collection' => $this->getName(),
                'gridfs'     => 1,
            ), $log));
        }
    }
}
