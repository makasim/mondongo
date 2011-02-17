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
class Query implements \Countable, \IteratorAggregate
{
    protected $repository;

    protected $criteria = array();
    protected $fields = array();
    protected $sort;
    protected $limit;
    protected $skip;

    /**
     * Constructor.
     *
     * @param string Mondongo\Repository $repository The repository of the class to query.
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
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
     * Set the criteria.
     *
     * @param array $criteria The criteria.
     */
    public function criteria(array $criteria)
    {
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
     */
    public function fields(array $fields)
    {
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
     */
    public function sort($sort)
    {
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
     */
    public function limit($limit)
    {
        if ($limit != (int) $limit) {
            throw new \InvalidArgumentException(sprintf('The limit "%s" is not valid.', $limit));
        }

        $this->limit = null === $limit ? null : (int) $limit;

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
     */
    public function skip($skip)
    {
        if ($skip != (int) $skip) {
            throw new \InvalidArgumentException(sprintf('The skip "%s" is not valid.', $skip));
        }

        $this->skip = null === $skip ? null : (int) $skip;

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
     * Returns an \ArrayIterator with results (implements \IteratorAggregate interface).
     *
     * The query is executed here.
     *
     * @return \ArrayIterator An array iterator with the results.
     */
    public function getIterator()
    {
        $documentClass = $this->repository->getDocumentClass();
        $isFile = $this->repository->isFile();
        $identityMap = $this->repository->getIdentityMap();

        $results = array();
        foreach ($this->createCursor() as $data) {
            $id = $isFile ? $data->file['_id'] : $data['_id'];
            if ($identityMap->hasById($id)) {
                $results[] = $identityMap->getById($id);
                continue;
            }

            $results[] = $document = new $documentClass();
            if ($isFile) {
                $file = $data;
                $data = $file->file;
                $data['file'] = $file;
            }
            $document->setDocumentData($data);

            $identityMap->add($document);
        }

        return new \ArrayIterator($results);
    }

    /**
     * Returns all the results.
     *
     * @return array An array with all the results.
     */
    public function all()
    {
        return iterator_to_array($this);
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

        return $cursor;
    }
}
