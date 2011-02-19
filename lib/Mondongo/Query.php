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
 * Query.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Query implements \Countable, \Iterator
{
    protected $repository;
    protected $documentClass;
    protected $isFile;

    protected $cursor;
    protected $identityMapDocuments;

    protected $criteria = array();
    protected $fields = array();
    protected $sort;
    protected $limit;
    protected $skip;
    protected $batchSize;
    protected $hint;
    protected $snapshot = false;
    protected $tailable = false;
    protected $timeout;

    /**
     * Constructor.
     *
     * @param string Mondongo\Repository $repository The repository of the class to query.
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->documentClass = $repository->getDocumentClass();
        $this->isFile = $repository->isFile();
    }

    /**
     * Returns the repository.
     *
     * @return Mondongo\Repository The repository.
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Returns the current cursor when the query is executed iterating.
     *
     * @return \MongoCursor|null The cursor or null if there is not cursor.
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    protected function startCursor()
    {
        $this->cursor = $this->createCursor();
        $this->identityMapDocuments =& $this->repository->getIdentityMap()->allByReference();

        return $this->cursor;
    }

    /**
     * Reset the cursor.
     *
     * You have to use this method if you don't end to iterate the query and you want
     * continue working with the query.
     */
    public function resetCursor()
    {
        $this->cursor = null;
        unset($this->identityMapDocuments);
    }

    /**
     * Set the criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return Mondongo\Query The query instance (fluent interface).
     *
     * @throws \InvalidArgumentException If the criteria is not an array or null.
     */
    public function criteria($criteria)
    {
        $this->checkCursor();

        if (null !== $criteria && !is_array($criteria)) {
            throw new \InvalidArgumentException(sprintf('The criteria "%s" is not valid.', $criteria));
        }

        $this->criteria = $criteria;

        return $this;
    }

    /**
     * Returns the criteria.
     *
     * @return array The criteria.
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Set the fields.
     *
     * @param array $fields The fields.
     *
     * @return Mondongo\Query The query instance (fluent interface).
     *
     * @throws \InvalidArgumentException If the fields are not an array or null.
     */
    public function fields($fields)
    {
        $this->checkCursor();

        if (null !== $fields && !is_array($fields)) {
            throw new \InvalidArgumentException(sprintf('The fields "%s" are not valid.', $fields));
        }

        $this->fields = $fields;

        return $this;
    }

    /**
     * Returns the fields.
     *
     * @return array The fields.
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set the sort.
     *
     * @param array|null $sort The sort.
     *
     * @return Mondongo\Query The query instance (fluent interface).
     *
     * @throws \InvalidArgumentException If the sort is not an array or null.
     */
    public function sort($sort)
    {
        $this->checkCursor();

        if (null !== $sort && !is_array($sort)) {
            throw new \InvalidArgumentException(sprintf('The sort "%s" is not valid.', $sort));
        }

        $this->sort = $sort;

        return $this;
    }

    /**
     * Returns the sort.
     *
     * @return array The sort.
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set the limit.
     *
     * @param int|null $limit The limit.
     *
     * @return Mondongo\Query The query instance (fluent interface).
     *
     * @throws \InvalidArgumentException If the limit is not a valid integer or null.
     */
    public function limit($limit)
    {
        $this->checkCursor();

        if (null !== $limit) {
            if (!is_numeric($limit) || $limit != (int) $limit) {
                throw new \InvalidArgumentException(sprintf('The limit "%s" is not valid.', $limit));
            }
            $limit = (int) $limit;
        }

        $this->limit = $limit;

        return $this;
    }

    /**
     * Returns the limit.
     *
     * @return int|null The limit.
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the skip.
     *
     * @param int|null $skip The skip.
     *
     * @return Mondongo\Query The query instance (fluent interface).
     *
     * @throws \InvalidArgumentException If the skip is not a valid integer, or null.
     */
    public function skip($skip)
    {
        $this->checkCursor();

        if (null !== $skip) {
            if (!is_numeric($skip) || $skip != (int) $skip) {
                throw new \InvalidArgumentException(sprintf('The skip "%s" is not valid.', $skip));
            }
            $skip = (int) $skip;
        }

        $this->skip = $skip;

        return $this;
    }

    /**
     * Returns the skip.
     *
     * @return int|null The skip.
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * Set the batch size.
     *
     * @param int|null $batchSize The batch size.
     *
     * @return Mondongo\Query The query instance (fluent interface).
     */
    public function batchSize($batchSize)
    {
        $this->checkCursor();

        if (null !== $batchSize) {
            if (!is_numeric($batchSize) || $batchSize != (int) $batchSize) {
                throw new \InvalidArgumentException(sprintf('The batchSize "%s" is not valid.', $batchSize));
            }
            $batchSize = (int) $batchSize;
        }

        $this->batchSize = $batchSize;

        return $this;
    }

    /**
     * Returns the batch size.
     *
     * @return int|null The batch size.
     */
    public function getBatchSize()
    {
        return $this->batchSize;
    }

    /**
     * Set the hint.
     *
     * @param array|null The hint.
     *
     * @return Mondongo\Query The query instance (fluent interface).
     */
    public function hint($hint)
    {
        $this->checkCursor();

        if (null !== $hint && !is_array($hint)) {
            throw new \InvalidArgumentException(sprintf('The hint "%s" is not valid.', $hint));
        }

        $this->hint = $hint;

        return $this;
    }

    /**
     * Returns the hint.
     *
     * @return array|null The hint.
     */
    public function getHint()
    {
        return $this->hint;
    }

    /**
     * Set if the snapshot mode is used.
     *
     * @param bool $snapshot If the snapshot mode is used.
     *
     * @return Mondongo\Query The query instance (fluent interface).
     */
    public function snapshot($snapshot)
    {
        $this->checkCursor();

        if (!is_bool($snapshot)) {
            throw new \InvalidArgumentException('The snapshot is not a boolean.');
        }

        $this->snapshot = $snapshot;

        return $this;
    }

    /**
     * Returns if the snapshot mode is used.
     *
     * @return bool If the snapshot mode is used.
     */
    public function getSnapshot()
    {
        return $this->snapshot;
    }

    /**
     * Set if the query is tailable.
     *
     * @param bool $tailable If the query is tailable.
     *
     * @return Mondongo\Query The query instance (fluent interface).
     */
    public function tailable($tailable)
    {
        $this->checkCursor();

        if (!is_bool($tailable)) {
            throw new \InvalidArgumentException('The tailable is not a boolean.');
        }

        $this->tailable = $tailable;

        return $this;
    }

    /**
     * Returns if the query is tailable.
     *
     * @return bool If the query is tailable.
     */
    public function getTailable()
    {
        return $this->tailable;
    }

    /**
     * Set the timeout.
     *
     * @param int|null $timeout The timeout of the cursor.
     *
     * @return Mondongo\Query The query instance (fluent interface).
     */
    public function timeout($timeout)
    {
        $this->checkCursor();

        if (null !== $timeout) {
            if (!is_numeric($timeout) || $timeout != (int) $timeout) {
                throw new \InvalidArgumentException(sprintf('The limit "%s" is not valid.', $timeout));
            }
            $timeout = (int) $timeout;
        }

        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Returns the timeout.
     *
     * @return int|null The timeout.
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /*
     * Iterator interface.
     */
    public function rewind()
    {
        $this->startCursor()->rewind();
    }

    public function current()
    {
        $data = $this->cursor->current();

        $id = $this->isFile ? $data->file['_id']->__toString() : $data['_id']->__toString();
        if (isset($this->identityMapDocuments[$id])) {
            $document = $this->identityMapDocuments[$id];
        } else {
            $document = new $this->documentClass();
            if ($this->isFile) {
                $file = $data;
                $data = $file->file;
                $data['file'] = $file;
            }
            $document->setDocumentData($data);

            $this->identityMapDocuments[$id] = $document;
        }

        return $document;
    }

    public function key()
    {
        return $this->cursor->key();
    }

    public function next()
    {
        $this->cursor->next();
    }

    public function valid()
    {
        if (!$valid = $this->cursor->valid()) {
            $this->resetCursor();
        }

        return $valid;
    }

    /**
     * Returns all the results.
     *
     * @return array An array with all the results.
     */
    public function all()
    {
        $documents = array();
        foreach ($this->startCursor() as $id => $data) {
            if (isset($this->identityMapDocuments[$id])) {
                $document = $this->identityMapDocuments[$id];
            } else {
                $document = new $this->documentClass();
                if ($this->isFile) {
                    $file = $data;
                    $data = $file->file;
                    $data['file'] = $file;
                }
                $document->setDocumentData($data);

                $this->identityMapDocuments[$id] = $document;
            }
            $documents[$id] = $document;
        }
        $this->resetCursor();

        return $documents;
    }

    /**
     * Returns one result.
     *
     * @return Mondongo\Document\Document|null A document or null if there is no any result.
     */
    public function one()
    {
        $currentLimit = $this->limit;
        $results = $this->limit(1)->all();
        $this->limit = $currentLimit;

        return $results ? array_shift($results) : null;
    }

    /**
     * Count the number of results of the query.
     *
     * @return int The number of results of the query.
     */
    public function count()
    {
        return $this->createCursor()->count();
    }

    /**
     * Create a cursor with the data of the query.
     *
     * @return \MongoCursor A cursor with the data of the query.
     */
    public function createCursor()
    {
        $cursor = $this->repository->collection()->find($this->criteria, $this->fields);

        if (null !== $this->sort) {
            $cursor->sort($this->sort);
        }

        if (null !== $this->limit) {
            $cursor->limit($this->limit);
        }

        if (null !== $this->skip) {
            $cursor->skip($this->skip);
        }

        if (null !== $this->batchSize) {
            $cursor->batchSize($this->batchSize);
        }

        if (null !== $this->hint) {
            $cursor->hint($this->hint);
        }

        if ($this->snapshot) {
            $cursor->snapshot();
        }

        if ($this->tailable) {
            $cursor->tailable();
        }

        if (null !== $this->timeout) {
            $cursor->timeout($this->timeout);
        }

        return $cursor;
    }

    protected function checkCursor()
    {
        if (null !== $this->cursor) {
            throw new \LogicException('There is cursor, that is that you have not ended to iterate. If you want to continue working with the query you have to reset the cursor explicitly with the ->resetCursor() method.');
        }
    }
}
