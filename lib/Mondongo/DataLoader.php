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

        // process
        $data = $this->data;
        $documents = array();
        do {
            $change = false;
            foreach ($data as $class => $datum) {
                $map = $class::getDataMap();

                // references processed?
                foreach ($map['references'] as $name => $reference) {
                    if (!isset($documents[$reference['class']])) {
                        foreach ($datum as $value) {
                            if (isset($value[$name])) {
                                continue 3;
                            }
                        }
                    }
                }

                // create
                foreach ($datum as $key => $value) {
                    // references
                    foreach ($map['references'] as $name => $reference) {
                        if (!isset($value[$name])) {
                            continue;
                        }

                        // one
                        if ('one' == $reference['type']) {
                            if (!isset($documents[$reference['class']][$value[$name]])) {
                                throw new \RuntimeException(sprintf('The reference "%s" (%s) for the class "%s" does not exists.', $value[$name], $name, $class));
                            }
                            $value[$name] = $documents[$reference['class']][$value[$name]];
                        // many
                        } else {
                            $refs = array();
                            foreach ($value[$name] as $valum) {
                                if (!isset($documents[$reference['class']][$valum])) {
                                    throw new \RuntimeException(sprintf('The reference "%s" (%s) for the class "%s" does not exists.', $valum, $name, $class));
                                }
                                $refs[] = $documents[$reference['class']][$valum];
                            }
                            $value[$name] = $refs;
                        }
                    }

                    $documents[$class][$key] = $document = new $class();
                    $document->fromArray($value);
                    $this->mondongo->persist($document);
                }

                $change = true;
                unset($data[$class]);
            }
        } while ($data && $change);

        if (!$change) {
            throw new \RuntimeException('Unable to process everything.');
        }

        $this->mondongo->flush();
    }
}
