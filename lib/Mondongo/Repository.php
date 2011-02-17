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
     * Returns the identity map.
     *
     * @return \Mondongo\IdentityMap The identity map.
     */
    public function getIdentityMap()
    {
        return $this->identityMap;
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
    public function collection()
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
     * Create a query for the repository document class.
     *
     * @param array $criteria The criteria for the query (optional).
     *
     * @return Mondongo\Query The query.
     */
    public function query(array $criteria = array())
    {
        $query = new Query($this);
        $query->criteria($criteria);

        return $query;
    }

    /**
     * Find one document by id.
     *
     * @param \MongoId|string $id The document \MongoId or the identifier string.
     *
     * @return mixed The document or NULL if it does not exists.
     */
    public function find($id)
    {
        if (is_string($id)) {
            $id = new \MongoId($id);
        }

        if ($this->identityMap->hasById($id)) {
            return $this->identityMap->getById($id);
        }

        return $this->query(array('_id' => $id))->one();
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
        return $this->collection()->count($query);
    }

    /**
     * Remove documents.
     *
     * @param array $query The query (optional, by default an empty array).
     *
     * @return mixed The result of the remove collection method.
     */
    public function remove(array $query = array())
    {
        return $this->collection()->remove($query, array('safe' => true));
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
                        $id = $this->collection()->storeFile($file, $data, array('safe' => true));
                    // bytes
                    } else {
                        $id = $this->collection()->storeBytes($file, $data, array('safe' => true));
                    }

                    $result = $this->collection()->findOne(array('_id' => $id));

                    $data = $result->file;
                    $data['file'] = $result;
                }
            // normal
            } else {
                $this->collection()->batchInsert($a, array('safe' => true));
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

                $this->collection()->update(array('_id' => $document->getId()), $query, array('safe' => true));

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

        $this->collection()->remove(array('_id' => array('$in' => $ids)), array('safe' => true));

        foreach ($documents as $document) {
            $this->identityMap->remove($document);

            // postDelete event
            $document->postDeleteExtensions();
            $document->postDelete();
        }
    }
}
