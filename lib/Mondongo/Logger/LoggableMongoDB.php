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
 * A loggable MongoDB.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class LoggableMongoDB extends \MongoDB
{
    protected $mongo;

    /**
     * Constructor.
     *
     * @param \Mondongo\Logger\LoggableMongo $mongo A LoggableMongo instance.
     * @param string                         $name  The database name.
     */
    public function __construct(LoggableMongo $mongo, $name)
    {
        $this->mongo = $mongo;

        return parent::__construct($mongo, $name);
    }

    /**
     * Returns the LoggableMongo.
     *
     * @return \Mondongo\Logger\LoggableMongo The LoggableMongo.
     */
    public function getMongo()
    {
        return $this->mongo;
    }

    /**
     * Log.
     *
     * @param array $log The log.
     */
    public function log(array $log)
    {
        $this->mongo->log(array_merge(array(
            'database' => $this->__toString()
        ), $log));
    }

    /**
     * Proxy.
     */
    public function selectCollection($name)
    {
        return new LoggableMongoCollection($this, $name);
    }

    /**
     * Proxy.
     */
    public function __get($name)
    {
        return $this->selectCollection($name);
    }

    /*
     * Proxy.
     */
    public function getGridFS($prefix = 'fs')
    {
        return new LoggableMongoGridFS($this, $prefix);
    }
}
