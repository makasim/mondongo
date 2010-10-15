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
 * The base class for repositories.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class Repository
{
    /*
     * abstract
     *
     * protected $documentClass;
     * protected $connectionName;
     * protected $collectionName;
     */

    protected $mondongo;

    protected $connection;

    protected $collection;

    /**
     * Constructor.
     *
     * @param Mondongo\Mondongo $mondongo The Mondongo.
     *
     * @return void
     */
    public function __construct(Mondongo $mondongo)
    {
        $this->mondongo = $mondongo;
    }

    /**
     * Returns the document class.
     *
     * @return string The document class.
     */
    public function getDocumentClass()
    {
        return $this->documentClass;
    }

    /**
     * Returns the collection name.
     *
     * @returns string The collection name.
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * Returns the connection name.
     *
     * @returns string The connection name.
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Returns the Mondongo.
     *
     * @return Mondongo\Mondongo The Mondongo.
     */
    public function getMondongo()
    {
        return $this->mondongo;
    }

    /**
     * Returns the connection.
     *
     * @return Mondongo\Connection The connection.
     */
    public function getConnection()
    {
        if (!$this->connection) {
            if ($this->connectionName) {
                $this->connection = $this->mondongo->getConnection($this->connectionName);
            } else {
                $this->connection = $this->mondongo->getDefaultConnection();
            }
        }

        return $this->connection;
    }

    /**
     * Returns the collection.
     *
     * @return \MongoCollection The collection.
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $connection = $this->getConnection();
            // loggable
            if ($loggerCallable = $this->mondongo->getLoggerCallable()) {
                $this->collection = new LoggableMongoCollection($connection->getMongo(), $connection->getMongoDB(), $this->collectionName);
                $this->collection->setLoggerCallable($loggerCallable);
            // normal
            } else {
                $this->collection = new \MongoCollection($connection->getMongoDB(), $this->collectionName);
            }
        }

        return $this->collection;
    }

    /**
     * Find documents.
     *
     * Options:
     *
     *   * query:  the query (array)
     *   * fields: the fields (array)
     *   * sort:   the sort
     *   * limit:  the limit
     *   * skip:   the skip
     *   * one:    if returns one result (incompatible with limit)
     *
     * @param array $options An array of options.
     *
     * @return mixed The document/s found within the parameters.
     */
    public function find(array $options = array())
    {
        // query
        if (!isset($options['query'])) {
            $options['query'] = array();
        }

        // fields
        if (!isset($options['fields'])) {
            $options['fields'] = array();
        }

        // cursor
        $cursor = $this->getCollection()->find($options['query'], $options['fields']);

        // sort
        if (isset($options['sort'])) {
            $cursor->sort($options['sort']);
        }

        // one
        if (isset($options['one'])) {
            $cursor->limit(1);
        // limit
        } elseif (isset($options['limit'])) {
            $cursor->limit($options['limit']);
        }

        // skip
        if (isset($options['skip'])) {
            $cursor->skip($options['skip']);
        }

        // results
        $results = array();
        foreach ($cursor as $c) {
            $results[] = $d = new $this->documentClass();
            $d->setDocumentData($c);
        }

        if ($results) {
            // one
            if (isset($options['one'])) {
                return array_shift($results);
            }

            return $results;
        }

        return null;
    }

    /**
     * Find one document.
     *
     * @param array $options An array of options.
     *
     * @return mixed The document found within the parameters.
     *
     * @see ::find()
     */
    public function findOne(array $options = array())
    {
        return $this->find(array_merge($options, array('one' => true)));
    }

    /**
     * Find one document by id.
     *
     * @param \MongoId $id The document \MongoId.
     *
     * @return mixed The document or NULL if it does not exists.
     */
    public function findOneById(\MongoId $id)
    {
        return $this->find(array('query' => array('_id' => $id), 'one' => true));
    }

    /**
     * Count documents.
     *
     * @param array $query The query (opcional, by default an empty array).
     *
     * @return integer The number of documents.
     */
    public function count(array $query = array())
    {
        return $this->getCollection()->find($query)->count();
    }

    /**
     * Remove documents.
     *
     * @param array $query The query (optional, by default an empty array).
     *
     * @return void
     */
    public function remove(array $query = array())
    {
        $this->getCollection()->remove($query, array('safe' => true));
    }

    /**
     * Save documents.
     *
     * @param mixed $documents A document or an array of documents.
     *
     * @return void
     */
    public function save($documents)
    {
        if (!is_array($documents)) {
            $documents = array($documents);
        }

        $inserts = array();
        $updates = array();

        foreach ($documents as $document) {
            if ($document->isNew()) {
                $inserts[spl_object_hash($document)] = $document;
            } else {
                $updates[] = $document;
            }
        }

        // insert
        if ($inserts) {
            $a = array();
            foreach ($inserts as $oid => $document) {
                // preInsert event
                $document->preInsertExtensions();
                $document->preInsert();

                // preSave event
                $document->preSaveExtensions();
                $document->preSave();

                $a[$oid] = $document->getQueryForSave();
            }

            $this->getCollection()->batchInsert($a, array('safe' => true));

            foreach ($a as $oid => $data) {
                $inserts[$oid]->setId($data['_id']);
                $inserts[$oid]->clearModified();

                // postInsert event
                $inserts[$oid]->postInsertExtensions();
                $inserts[$oid]->postInsert();

                // postSave event
                $inserts[$oid]->postSaveExtensions();
                $inserts[$oid]->postSave();
            }
        }

        // update
        if ($updates) {
            foreach ($updates as $document) {
                // preUpdate event
                $document->preUpdateExtensions();
                $document->preUpdate();

                // preSave event
                $document->preSaveExtensions();
                $document->preSave();

                $query = $document->getQueryForSave();

                $this->getCollection()->update(array('_id' => $document->getId()), $query, array('safe' => true));

                $document->clearModified();

                // postUpdate event
                $document->postUpdateExtensions();
                $document->postUpdate();

                // postSave event
                $document->postSaveExtensions();
                $document->postSave();
            }
        }
    }

    /**
     * Delete documents.
     *
     * @param mixed $documents A document or an array of documents.
     *
     * @return void
     */
    public function delete($documents)
    {
        if (!is_array($documents)) {
            $documents = array($documents);
        }

        $ids = array();
        foreach ($documents as $document) {
            $ids[] = $document->getId();

            // preDelete event
            $document->preDeleteExtensions();
            $document->preDelete();
        }

        $this->getCollection()->remove(array('_id' => array('$in' => $ids)), array('safe' => true));

        foreach ($documents as $document) {
            // postDelete event
            $document->postDeleteExtensions();
            $document->postDelete();
        }
    }
}
