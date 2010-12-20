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
 * The Mondongo DataLoader.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class DataLoader
{
    protected $mondongo;
    protected $data;

    /**
     * Constructor.
     *
     * @param \Mondongo\Mondongo $mondongo The Mondongo.
     * @param array              $data     The data.
     */
    public function __construct(Mondongo $mondongo, array $data = array())
    {
        $this->setMondongo($mondongo);
        $this->setData($data);
    }

    /**
     * Set the Mondongo.
     *
     * @param \Mondongo\Mondongo $mondongo The Mondongo.
     */
    public function setMondongo(Mondongo $mondongo)
    {
        $this->mondongo = $mondongo;
    }

    /**
     * Returns the Mondongo.
     *
     * @return \Mondongo\Mondongo The Mondongo.
     */
    public function getMondongo()
    {
        return $this->mondongo;
    }

    /**
     * Set the data.
     *
     * @param array $data The data.
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns the data.
     *
     * @return array The data.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Load the data.
     *
     * @param bool $purge If purge the databases before load the data.
     *
     * @throws \RuntimeException If the Mondongo's UnitOfWork has pending operations.
     */
    public function load($purge = false)
    {
        // has pending
        if ($this->mondongo->getUnitOfWork()->hasPending()) {
            throw new \RuntimeException('The Mondongo\'s Unit of Work has pending operations.');
        }

        // purge
        if ($purge) {
            foreach ($this->mondongo->getConnections() as $connection) {
                $connection->getMongoDB()->drop();
            }
        }

        // vars
        $mondongo  = $this->mondongo;
        $data      = $this->data;
        $documents = array();

        $maps = array();
        foreach ($data as $class => $datum) {
            $maps[$class] = $class::getDataMap();
        }

        // process function
        $process = function ($class, $key) use (&$process, $mondongo, &$data, &$documents, &$maps) {
            static $processed = array();

            if (isset($processed[$class][$key])) {
                return;
            }

            if (!isset($data[$class][$key])) {
                throw new \RuntimeException(sprintf('The document "%s" of the class "%s" does not exist.', $key, $class));
            }
            $datum = $data[$class][$key];

            // references
            foreach ($maps[$class]['references'] as $name => $reference) {
                if (!isset($datum[$name])) {
                    continue;
                }

                // one
                if ('one' == $reference['type']) {
                    $process($reference['class'], $datum[$name]);

                    if (!isset($documents[$reference['class']][$datum[$name]])) {
                        throw new \RuntimeException(sprintf('The reference "%s" (%s) for the class "%s" does not exists.', $datum[$name], $name, $class));
                    }
                    $datum[$name] = $documents[$reference['class']][$datum[$name]];
                // many
                } else {
                    $refs = array();
                    foreach ($datum[$name] as $value) {
                        $process($reference['class'], $value);

                        if (!isset($documents[$reference['class']][$value])) {
                            throw new \RuntimeException(sprintf('The reference "%s" (%s) for the class "%s" does not exists.', $value, $name, $class));
                        }
                        $refs[] = $documents[$reference['class']][$value];
                    }
                    $datum[$name] = $refs;
                }
            }

            // document
            $documents[$class][$key] = $document = new $class();
            $document->fromArray($datum);
            $mondongo->persist($document);

            $processed[$class][$key] = true;
            unset($data[$class][$key]);
        };

        // process
        foreach ($data as $class => $datum) {
            foreach ($datum as $key => $value) {
                $process($class, $key);
            }
        }

        // flush
        $this->mondongo->flush();
    }

    protected function processDocument($class, $value, $references, &$documents)
    {
        # code...
    }
}
