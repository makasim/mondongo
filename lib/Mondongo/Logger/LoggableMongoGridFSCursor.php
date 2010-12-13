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
 * A loggable MongoGridFSCursor.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class LoggableMongoGridFSCursor extends \MongoGridFSCursor
{
    protected $grid;

    protected $type;
    protected $explainCursor;
    protected $time;

    /**
     * Constructor.
     */
    public function __construct(LoggableMongoGridFS $grid, array $query = array(), array $fields = array(), $type = 'find')
    {
        $this->grid = $grid;

        $mongo = $grid->getDB()->getMongo();
        $ns = $grid->getDB()->__toString().'.'.$grid->getName();

        $this->type = $type;
        $this->explainCursor = new \MongoGridFSCursor($grid, $mongo, $ns, $query, $fields);
        $this->time = new Time();

        parent::__construct($grid, $mongo, $ns, $query, $fields);
    }

    /**
     * Returns the LoggableMongoGridFS.
     *
     * @return \Mondongo\Logger\LoggableMongoGridFS The LoggableMongoGridFS.
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * Log.
     *
     * @param array $log The log.
     */
    public function log(array $log)
    {
        $this->grid->log($log);
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
     * getNext.
     */
    public function getNext()
    {
        $this->logQuery();

        return parent::getNext();
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
        $this->time->start();
        $return = parent::count($foundOnly);
        $time = $this->time->stop();

        $info = $this->info();

        $this->log(array(
            'type'      => 'count',
            'query'     => is_array($info['query']) ? $info['query'] : array(),
            'limit'     => $info['limit'],
            'skip'      => $info['skip'],
            'foundOnly' => $foundOnly,
            'time'      => $time,
        ));

        return $return;
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
                'type' => $this->type,
                'info' => $infoLog,
                'explain' => array(
                    'nscanned'        => $explain['nscanned'],
                    'nscannedObjects' => $explain['nscannedObjects'],
                    'n'               => $explain['n'],
                    'indexBounds'     => $explain['indexBounds'],
                ),
                'time' => $explain['millis'],
            ));
        }
    }
}
