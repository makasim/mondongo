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
 * Mondongo.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Mondongo
{
    const VERSION = '1.0.0-DEV';

    protected $unitOfWork;

    protected $loggerCallable;

    protected $connections = array();

    protected $defaultConnectionName;

    /**
     * Constructor.
     */
    public function __construct($loggerCallable = null)
    {
        $this->unitOfWork = new UnitOfWork($this);
        $this->loggerCallable = $loggerCallable;
    }

    /**
     * Returns the UnitOfWork.
     *
     * @return \Mondongo\UnitOfWork The UnitOfWork.
     */
    public function getUnitOfWork()
    {
        return $this->unitOfWork;
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
     * Set a connection.
     *
     * @param string              $name       The connection name.
     * @param Mondongo\Connection $connection The connection.
     *
     * @return void
     */
    public function setConnection($name, Connection $connection)
    {
        if (null !== $this->loggerCallable) {
            $connection->setLoggerCallable($this->loggerCallable);
            $connection->setLogDefault(array('connection' => $name));
        } else {
            $connection->setLoggerCallable(null);
        }

        $this->connections[$name] = $connection;
    }

    /**
     * Set the connections.
     *
     * @param array $connections An array of connections.
     *
     * @return void
     */
    public function setConnections(array $connections)
    {
        $this->connections = array();
        foreach ($connections as $name => $connection) {
            $this->setConnection($name, $connection);
        }
    }

    /**
     * Remove a connection.
     *
     * @param string $name The connection name.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the connection does not exists.
     */
    public function removeConnection($name)
    {
        if (!$this->hasConnection($name)) {
            throw new \InvalidArgumentException(sprintf('The connection "%s" does not exists.', $name));
        }

        unset($this->connections[$name]);
    }

    /**
     * Clear the connections.
     *
     * @return void
     */
    public function clearConnections()
    {
        $this->connections = array();
    }

    /**
     * Returns if a connection exists.
     *
     * @param string $name The connection name.
     *
     * @return boolean Returns if a connection exists.
     */
    public function hasConnection($name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * Return a connection.
     *
     * @param string $name The connection name.
     *
     * @return Mondongo\Connection The connection.
     *
     * @throws \InvalidArgumentException If the connection does not exists.
     */
    public function getConnection($name)
    {
        if (!$this->hasConnection($name)) {
            throw new \InvalidArgumentException(sprintf('The connection "%s" does not exists.', $name));
        }

        return $this->connections[$name];
    }

    /**
     * Returns the connections.
     *
     * @return array The array of connections.
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Set the default connection name.
     *
     * @param string $name The connection name.
     *
     * @return void
     */
    public function setDefaultConnectionName($name)
    {
        $this->defaultConnectionName = $name;
    }

    /**
     * Returns the default connection name.
     *
     * @return string The default connection name.
     */
    public function getDefaultConnectionName()
    {
        return $this->defaultConnectionName;
    }

    /**
     * Returns the default connection.
     *
     * @return Mondongo\Connection The default connection.
     *
     * @throws \RuntimeException If the default connection does not exists.
     * @throws \RuntimeException If there is not connections.
     */
    public function getDefaultConnection()
    {
        if (null !== $this->defaultConnectionName) {
            if (!isset($this->connections[$this->defaultConnectionName])) {
                throw new \RuntimeException(sprintf('The default connection "%s" does not exists.', $this->defaultConnectionName));
            }

            $connection = $this->connections[$this->defaultConnectionName];
        } elseif (!$connection = reset($this->connections)) {
            throw new \RuntimeException('There is not connections.');
        }

        return $connection;
    }

    /**
     * Returns repositories by document class.
     *
     * @param string $documentClass The document class.
     *
     * @return Mondongo\Repository The repository.
     */
    public function getRepository($documentClass)
    {
        if (!isset($this->repositories[$documentClass])) {
            $repositoryClass = $documentClass.'Repository';

            if (!class_exists($repositoryClass)) {
                throw new \Exception(sprintf('The class "%s" does not exists.', $repositoryClass));
            }

            $this->repositories[$documentClass] = new $repositoryClass($this);
        }

        return $this->repositories[$documentClass];
    }

    /**
     * Access to repository ->find() method.
     *
     * The first argument is the documentClass of repository.
     *
     * @see Mondongo\Repository::find()
     */
    public function find($documentClass, array $query = array(), array $options = array())
    {
        return $this->getRepository($documentClass)->find($query, $options);
    }

    /**
     * Access to repository ->findOne() method.
     *
     * The first argument is the documentClass of repository.
     *
     * @see Mondongo\Repository::findOne()
     */
    public function findOne($documentClass, array $query = array(), array $options = array())
    {
        return $this->getRepository($documentClass)->findOne($query, $options);
    }

    /**
     * Access to repository ->findOneById() method.
     *
     * The first argument is the documentClass of repository.
     *
     * @see Mondongo\Repository::findOneById()
     */
    public function findOneById($documentClass, $id)
    {
        return $this->getRepository($documentClass)->findOneById($id);
    }

    /**
     * Access to repository ->count() method.
     *
     * The first argument is the documentClass of repository.
     *
     * @see Mondongo\Repository::count()
     */
    public function count($documentClass, array $query = array())
    {
        return $this->getRepository($documentClass)->count($query);
    }

    /**
     * Access to UnitOfWork ->persist() method.
     *
     * @see \Mondongo\UnitOfWork::persist()
     */
    public function persist($document)
    {
        $this->unitOfWork->persist($document);
    }

    /**
     * Access to UnitOfWork ->remove() method.
     *
     * @see \Mondongo\UnitOfWork::remove()
     */
    public function remove($document)
    {
        $this->unitOfWork->remove($document);
    }

    /**
     * Access to UnitOfWork ->commit() method.
     *
     * @see \Mondongo\UnitOfWork::commit()
     */
    public function flush()
    {
        $this->unitOfWork->commit();
    }
}
