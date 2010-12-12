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
 * A loggable MongoCursor.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class LoggableMongoCursor extends \MongoCursor
{
    protected $dbName;

    protected $collectionName;

    protected $loggerCallable;

    protected $connectionName;

    protected $explainCursor;

    /**
     * Constructor.
     */
    public function __construct(\Mongo $connection, $ns, array $query = array(), array $fields = array())
    {
        parent::__construct($connection, $ns, $query, $fields);

        list($this->dbName, $this->collectionName) = explode('.', $ns);

        $this->explainCursor = new \MongoCursor($connection, $ns, $query, $fields);
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
     * hasNext.
     */
    public function hasNext()
    {
        $this->logQuery();

        return parent::hasNext();
    }

    /*
     * rewind.
     */
    public function rewind()
    {
        $this->logQuery();

        return parent::rewind();
    }

    /*
     * next.
     */
    public function next()
    {
        $this->logQuery();

        return parent::next();
    }

    /*
     * count.
     */
    public function count($foundOnly = false)
    {
        $info = $this->info();

        $this->log(array(
            'count'     => 1,
            'query'     => $info['query'],
            'limit'     => $info['limit'],
            'skip'      => $info['skip'],
            'foundOnly' => $foundOnly,
        ));

        return parent::count($foundOnly);
    }

    /*
     * log the query.
     */
    protected function logQuery()
    {
        $info = $this->info();

        if (!$info['started_iterating']) {
            if (!is_array($info['query'])) {
                $info['query'] = array();
            }

            // explain cursor
            $this->explainCursor->fields($info['fields']);
            $this->explainCursor->limit($info['limit']);
            $this->explainCursor->skip($info['skip']);
            if (isset($info['batchSize'])) {
                $this->explainCursor->batchSize($info['batchSize']);
            }
            if (isset($info['query']['$orderby'])) {
                $this->explainCursor->sort($info['query']['$orderby']);
            }
            if (isset($info['query']['$hint'])) {
                $this->explainCursor->hint($info['query']['$hint']);
            }
            if (isset($info['query']['$snapshot'])) {
                $this->explainCursor->snapshot();
            }
            $explain = $this->explainCursor->explain();

            // info log
            $infoLog = array(
                'query'  => isset($info['query']['$query']) && is_array($info['query']['$query']) ? $info['query']['$query'] : array(),
                'fields' => $info['fields'],
            );
            if (isset($info['query']['$orderby'])) {
                $infoLog['sort'] = $info['query']['$orderby'];
            }
            if ($info['limit']) {
                $infoLog['limit'] = $info['limit'];
            }
            if ($info['skip']) {
                $infoLog['skip'] = $info['skip'];
            }
            if ($info['batchSize']) {
                $infoLog['batchSize'] = $info['batchSize'];
            }
            if (isset($info['query']['$hint'])) {
                $infoLog['hint'] = $info['query']['$hint'];
            }
            if (isset($info['query']['$snapshot'])) {
                $infoLog['snapshot'] = 1;
            }

            $this->log($log = array(
                'info' => $infoLog,
                'explain' => array(
                    'nscanned'        => $explain['nscanned'],
                    'nscannedObjects' => $explain['nscannedObjects'],
                    'n'               => $explain['n'],
                    'millis'          => $explain['millis'],
                    'indexBounds'     => $explain['indexBounds'],
                ),
            ));
        }
    }

    /*
     * log.
     */
    protected function log(array $log)
    {
        if ($this->loggerCallable) {
            call_user_func($this->loggerCallable, array_merge(array(
                'connection' => $this->connectionName,
                'database'   => $this->dbName,
                'collection' => $this->collectionName,
            ), $log));
        }
    }
}
