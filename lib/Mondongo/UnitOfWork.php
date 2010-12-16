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

use Mondongo\Document\Document;

/**
 * UnitOfWork.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class UnitOfWork
{
    protected $mondongo;

    protected $persist = array();
    protected $remove  = array();

    /**
     * Constructor.
     *
     * @param \Mondongo\Mondongo $mondongo The Mondongo.
     */
    public function __construct(Mondongo $mondongo)
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
     * Persist a document.
     *
     * @param \Mondongo\Document\Document $document A Mondongo document.
     */
    public function persist(Document $document)
    {
        $class = get_class($document);
        $oid   = spl_object_hash($document);

        $this->persist[$class][$oid] = $document;

        if (isset($this->remove[$class][$oid])) {
            unset($this->remove[$class][$oid]);
        }
    }

    /**
     * Returns if a document is pending for persist.
     *
     * @param \Mondongo\Document\Document A Mondongo document.
     *
     * @return bool If the document is pending for persist.
     */
    public function isPendingForPersist(Document $document)
    {
        return isset($this->persist[get_class($document)][spl_object_hash($document)]);
    }

    /**
     * Remove a document.
     *
     * @param \Mondongo\Document $document A Mondongo document.
     *
     * @throws \InvalidArgumentException If you pass a new document.
     */
    public function remove($document)
    {
        if ($document->isNew()) {
            throw new \InvalidArgumentException('You cannot remove a new document.');
        }

        $class = get_class($document);
        $oid   = spl_object_hash($document);

        $this->remove[$class][$oid] = $document;

        if (isset($this->persist[$class][$oid])) {
            unset($this->persist[$class][$oid]);
        }
    }

    /**
     * Returns if a document is pending for remove.
     *
     * @param \Mondongo\Document\Document A Mondongo document.
     *
     * @return bool If the document is pending for remove.
     */
    public function isPendingForRemove(Document $document)
    {
        return isset($this->remove[get_class($document)][spl_object_hash($document)]);
    }

    /**
     * Commit pending persist and remove operations.
     */
    public function commit()
    {
        // execute
        foreach ($this->persist as $class => $documents) {
            $this->mondongo->getRepository($class)->save($documents);
        }
        foreach ($this->remove as $class => $documents) {
            $this->mondongo->getRepository($class)->delete($documents);
        }

        // clear
        $this->clear();
    }

    /**
     * Clear the pending operations
     */
    public function clear()
    {
        $this->persist = array();
        $this->remove  = array();
    }
}
