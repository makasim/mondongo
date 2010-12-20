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
     * protected $isFile;
     */

    protected $mondongo;

    protected $connection;

    protected $collection;

    protected $identityMap;

    /**
     * Constructor.
     *
     * @param Mondongo\Mondongo $mondongo The Mondongo.
     *
     * @return void
     */
    public function __construct(Mondongo $mondongo)
    {
        $this->mondongo    = $mondongo;
        $this->identityMap = new IdentityMap();
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
     * Returns if the document is a file (if it use GridFS).
     *
     * @return bool If the document is a file.
     */
    public function isFile()
    {
        return $this->isFile;
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
            // gridfs
            if ($this->isFile) {
                $this->collection = $this->getConnection()->getMongoDB()->getGridFS($this->collectionName);
            // normal
            } else {
                $this->collection = $this->getConnection()->getMongoDB()->selectCollection($this->collectionName);
            }
        }

        return $this->collection;
    }

    /**
     * Returns the identity map.
     *
     * @return \Mondongo\IdentityMap The identity map.
     */
    public function getIdentityMap()
    {
        return $this->identityMap;
    }

    /**
     * Find documents.
     *
     * Options:
     *
     *   * fields: the fields (array)
     *   * sort:   the sort (array)
     *   * limit:  the limit
     *   * skip:   the skip
     *   * one:    if returns one result (incompatible with limit)
     *
     * @param array $query   The query (optional, an empty array by default)
     * @param array $options An array of options (optional).
     *
     * @return mixed The document/s found within the parameters.
     */
    public function find(array $query = array(), array $options = array())
    {
        // fields
        if (!isset($options['fields'])) {
            $options['fields'] = array();
        }

        // cursor
        $cursor = $this->getCollection()->find($query, $options['fields']);

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
        foreach ($cursor as $data) {
            $id = $this->isFile ? $data->file['_id'] : $data['_id'];
            if ($this->identityMap->hasById($id)) {
                $results[] = $this->identityMap->getById($id);
                continue;
            }

            $results[] = $document = new $this->documentClass();
            if ($this->isFile) {
                $file = $data;
                $data = $file->file;
                $data['file'] = $file;
            }
            $document->setDocumentData($data);

            $this->identityMap->add($document);
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
     * @param array $query   The query (optional, an empty array by default)
     * @param array $options An array of options (optional).
     *
     * @return mixed The document found within the parameters.
     *
     * @see ::find()
     */
    public function findOne(array $query = array(), array $options = array())
    {
        return $this->find($query, array_merge($options, array('one' => true)));
    }

    /**
     * Find one document by mongo id.
     *
     * @param \MongoId|string $id The document \MongoId or the identifier string.
     *
     * @return mixed The document or NULL if it does not exists.
     */
    public function findOneById($id)
    {
        if (is_string($id)) {
            $id = new \MongoId($id);
        }

        return $this->find(array('_id' => $id), array('one' => true));
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
        return $this->getCollection()->count($query);
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

        do {
            $inserts = array();
            $updates = array();

            $change = false;
            foreach ($documents as $document) {
                $documentData = $document->getDocumentData();

                $document->saveReferences();

                if ($document->getDocumentData() !== $documentData) {
                    $change = true;
                    break;
                }

                // only modified
                if (!$document->isModified()) {
                    continue;
                }

                if ($document->isNew()) {
                    $inserts[spl_object_hash($document)] = $document;
                } else {
                    $updates[] = $document;
                }
            }
        } while ($change);

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

            // GridFS
            if ($this->isFile) {
                foreach ($a as &$data) {
                    if (!isset($data['file'])) {
                        throw new \RuntimeException('The document has not file.');
                    }
                    $file = $data['file'];
                    unset($data['file']);

                    // file
                    if (file_exists($file)) {
                        $id = $this->getCollection()->storeFile($file, $data, array('safe' => true));
                    // bytes
                    } else {
                        $id = $this->getCollection()->storeBytes($file, $data, array('safe' => true));
                    }

                    $result = $this->getCollection()->findOne(array('_id' => $id));

                    $data = $result->file;
                    $data['file'] = $result;
                }
            // normal
            } else {
                $this->getCollection()->batchInsert($a, array('safe' => true));
            }

            foreach ($a as $oid => $data) {
                $inserts[$oid]->setId($data['_id']);
                $inserts[$oid]->clearModified();

                $this->identityMap->add($inserts[$oid]);

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
            $this->identityMap->remove($document);

            // postDelete event
            $document->postDeleteExtensions();
            $document->postDelete();
        }
    }
}
