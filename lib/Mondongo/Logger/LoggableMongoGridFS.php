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

namespace Mondongo\Logger;

/**
 * A loggable MongoGridFS.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class LoggableMongoGridFS extends \MongoGridFS
{
    protected $db;

    protected $time;

    /**
     * Constructor.
     *
     * @param \Mondongo\Logger\LoggableMongoDB $db     A LoggableMongoDB instance.
     * @param string                           $prefix The prefix (optional, fs by default).
     */
    public function __construct(LoggableMongoDB $db, $prefix = 'fs')
    {
        $this->db = $db;
        $this->time = new Time();

        parent::__construct($db, $prefix);
    }


    /**
     * Returns the LoggableMongoDB.
     *
     * @return \Mondongo\Logger\LoggableMongoDB The LoggableMongoDB
     */
    public function getDB()
    {
        return $this->db;
    }

    /**
     * Log.
     *
     * @param array $log The log.
     */
    public function log(array $log)
    {
        $this->db->log(array_merge(array(
            'collection' => $this->getName(),
            'gridfs'     => 1,
        ), $log));
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
        return new LoggableMongoGridFSCursor($this, $query, $fields);
    }

    /*
     * findOne.
     */
    public function findOne(array $query = array(), array $fields = array())
    {
        $cursor = new LoggableMongoGridFSCursor($this, $query, $fields, 'findOne');
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
}
