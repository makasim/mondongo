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
 * The identity map class.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class IdentityMap
{
    protected $documents = array();

    /**
     * Returns if exists a document by id.
     *
     * @param \MongoId $id The id.
     *
     * @return boolean If exists or no the document.
     */
    public function hasById(\MongoId $id)
    {
        return isset($this->documents[$id->__toString()]);
    }

    /**
     * Returns if exists a document.
     *
     * @param \Mondongo\Document\Document $document The document.
     *
     * @return boolean If the document exists.
     */
    public function has(\Mondongo\Document\Document $document)
    {
        return $this->hasById($document->getId());
    }

    /**
     * Add a document.
     *
     * @param \Mondongo\Document\Document $document The document.
     *
     * @return void
     */
    public function add(\Mondongo\Document\Document $document)
    {
        $this->documents[$document->getId()->__toString()] = $document;
    }

    /**
     * Returns a document by id
     *
     * @param \MongoId $id The id.
     *
     * @return \Mondongo\Document\Document The document.
     */
    public function getById(\MongoId $id)
    {
        return $this->documents[$id->__toString()];
    }

    /**
     * Returns all documents.
     *
     * @return array The documents.
     */
    public function all()
    {
        return $this->documents;
    }

    /**
     * Remove a document by id.
     *
     * @param \MongoId $id The id.
     *
     * @return void
     */
    public function removeById(\MongoId $id)
    {
        unset($this->documents[$id->__toString()]);
    }

    /**
     * Remove a document.
     *
     * @param \Mondongo\Document\Document $document The document.
     *
     * @return void
     */
    public function remove(\Mondongo\Document\Document $document)
    {
        $this->removeById($document->getId());
    }

    /**
     * Clear the documents.
     *
     * @return void
     */
    public function clear()
    {
        $this->documents = array();
    }
}
