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
 * Connection.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Connection
{
    protected $server;

    protected $options;

    protected $database;

    protected $mongo;

    protected $mongoDB;

    /**
     * Constructor.
     *
     * @param string $server   The server.
     * @param string $database The database name.
     * @param string $options  The \Mongo options.
     *
     * @return void
     */
    public function __construct($server, $database, array $options = array())
    {
        $this->server   = $server;
        $this->database = $database;
        $this->options  = $options;
    }

    /**
     * Returns the database object.
     *
     * @return \MongoDB The database object.
     */
    public function getMongoDB()
    {
        if (null === $this->mongoDB) {
            $this->mongo   = new \Mongo($this->server, $this->options);
            $this->mongoDB = $this->mongo->selectDB($this->database);
        }

        return $this->mongoDB;
    }
}
