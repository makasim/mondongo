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

namespace Mondongo\Document;

/**
 * The base class for documents.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class Document extends EmbeddedDocument
{
    protected $id;

    /**
     * Returns the collection.
     *
     * @return \MongoCollection The collection.
     */
    static public function collection()
    {
        return static::repository()->collection();
    }

    /**
     * Access to repository ->query() method.
     *
     * @see Mondongo\Repository::query()
     */
    static public function query(array $criteria = array())
    {
        return static::repository()->query($criteria);
    }

    /**
     * Access to repository ->find() method.
     *
     * @see Mondongo\Repository::find()
     */
    static public function find($id)
    {
        return static::repository()->find($id);
    }

    /**
     * Access to repository ->count() method.
     *
     * @see Mondongo\Repository::count()
     */
    static public function count(array $criteria = array())
    {
        return static::repository()->count($criteria);
    }

    /**
     * Set the document \MongoId.
     *
     * @param \MongoId|int|string $id The \MongoId object.
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the \MongoId of document.
     *
     * @return \MongoId|int|string|null The \MongoId of document if exists, null otherwise.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns if the document is new.
     *
     * @return bool Returns if the document is new.
     */
    public function isNew()
    {
        return null === $this->id;
    }

    /**
     * Refresh the document data from the database.
     *
     * @return void
     */
    public function refresh()
    {
        if ($this->isNew()) {
            throw new \LogicException('The document is new.');
        }

        foreach ($this->data as $type => &$values) {
            foreach ($values as &$value) {
                $value = null;
            }
        }

        $this->setDocumentData(static::collection()->findOne(array('_id' => $this->getId())));
    }

    /**
     * Save the document.
     *
     * @return void
     */
    public function save()
    {
        static::repository()->save($this);
    }

    /**
     * Delete the document.
     *
     * @return void
     */
    public function delete()
    {
        static::repository()->delete($this);
    }

    /**
     * Returns the query for save.
     *
     * @return array Returns the query for save.
     */
    public function getQueryForSave()
    {
        $query = array();

        return $this->queryDocument($query, $this, null, $this->isNew());
    }

    protected function queryDocument($query, $document, $name, $isNew)
    {
        $data = $document->getDocumentData();

        // fields
        if ($fieldsModified = $document->getFieldsModified()) {
            $fields = array();
            foreach (array_keys($fieldsModified) as $field) {
                if (null !== $value = $data['fields'][$field]) {
                    $fields[$field] = $value;
                }
            }

            if ($fields) {
                $fields = $document->fieldsToMongo($fields);
            }

            foreach (array_keys($fieldsModified) as $field) {
                // insert
                if ($isNew) {
                    // base
                    if (null === $name) {
                        $query[$field] = $fields[$field];
                    // embed
                    } else {
                        $q =& $query;
                        foreach ($name as $n) {
                            if (!isset($q[$n])) {
                                $q[$n] = array();
                            }
                            $q =& $q[$n];
                        }
                        $q[$field] = $fields[$field];
                    }
                // update
                } else {
                    $fieldName = (null !== $name ? implode('.', $name).'.' : '').$field;

                    // set
                    if (array_key_exists($field, $fields)) {
                        $query['$set'][$fieldName] = $fields[$field];
                    // unset
                    } else {
                        $query['$unset'][$fieldName] = 1;
                    }
                }
            }
        }

        // embeddeds
        if ($embeddedsChanged = $document->getEmbeddedsChanged()) {
            foreach ($embeddedsChanged as $embeddedName => $embeddedChanged) {
                $embeddedQueryName = null !== $name ? array_merge($name, array($embeddedName)) : array($embeddedName);

                // removed
                if (null === $data['embeddeds'][$embeddedName]) {
                    $query['$unset'][$embeddedQueryName] = 1;
                    continue;
                }

                // one
                if ($data['embeddeds'][$embeddedName] instanceof EmbeddedDocument) {
                    if (
                        // added
                        null === $embeddedChanged
                        ||
                        // same object
                        spl_object_hash($embeddedChanged) == spl_object_hash($data['embeddeds'][$embeddedName])
                    ) {
                        $element = $data['embeddeds'][$embeddedName];
                        $query = $this->queryDocument($query, $element, $embeddedQueryName, $isNew);
                        continue;
                    }

                    // changed object
                    $embeddedQuery = array();
                    $query['$set'][implode('.', $embeddedQueryName)] = $this->queryDocument($embeddedQuery, $data['embeddeds'][$embeddedName], null, true);
                    continue;
                }

                /*
                 * many
                 */
                $elements = $data['embeddeds'][$embeddedName]->getElements();

                // insert
                if ($this->isNew()) {
                    foreach ($elements as $key => $element) {
                        $query = $this->queryDocument($query, $element, array_merge($embeddedQueryName, array($key)), $isNew);
                    }
                // update
                } else {
                    $originalElements = $embeddedChanged;

                    // insert
                    foreach ($elements as $key => $element) {
                        if (!isset($originalElements[$key]) || spl_object_hash($element) != spl_object_hash($originalElements[$key])) {
                            $query['$pushAll'][implode('.', $embeddedQueryName)][] = $element->dataToMongo();
                        // update
                        } else {
                            $query = $this->queryDocument($query, $element, array_merge($embeddedQueryName, array($key)), $isNew);
                        }
                    }

                    // delete
                    if (null !== $originalElements) {
                        foreach ($originalElements as $key => $element) {
                            if (!isset($elements[$key]) || spl_object_hash($element) != spl_object_hash($elements[$key])) {
                                $query['$pullAll'][implode('.', $embeddedQueryName)][] = $element->dataToMongo();
                            }
                        }
                    }
                }
            }
        }

        return $query;
    }
}
