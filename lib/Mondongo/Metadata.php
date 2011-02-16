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
 * Metadata.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class Metadata
{
    /*
     * abstract
     *
     * protected $classes;
     */

    protected $infoClass;

    /**
     * Returns the classes.
     *
     * @return array The classes.
     */
    public function getClasses()
    {
        return array_keys($this->classes);
    }

    /**
     * Returns the classes of documents (not embeddeds).
     *
     * @return array The classes of documents.
     */
    public function getDocumentsClasses()
    {
        $classes = array();
        foreach ($this->classes as $class => $isEmbedded) {
            if (!$isEmbedded) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * Returns the classes of embeddeds documents.
     *
     * @return array The classes of embeddeds documents.
     */
    public function getEmbeddedDocumentsClasses()
    {
        $classes = array();
        foreach ($this->classes as $classs => $isEmbedded) {
            if ($isEmbedded) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * Returns if a class exists.
     *
     * @param string $class The class.
     *
     * @return bool Returns if a class exists.
     */
    public function hasClass($class)
    {
        return isset($this->classes[$class]);
    }

    /**
     * Returns if a class is a document (not embedded).
     *
     * @param string $class A class.
     *
     * @return bool If the class is a document (not embedded).
     *
     * @throws \InvalidArgumentException If the class does not exist in the metadata.
     */
    public function isDocumentClass($class)
    {
        if (!$this->hasClass($class)) {
            throw new \InvalidArgumentException(sprintf('The class "%s" does not exist.', $class));
        }

        return !$this->classes[$class];
    }

    /**
     * Returns if a class is a embedded document.
     *
     * @param string $class A class.
     *
     * @return bool If the class is a embedded document.
     *
     * @throws \InvalidArgumentException If the class does not exist in the metadata.
     */
    public function isDocumentEmbeddedClass($class)
    {
        if (!$this->hasClass($class)) {
            throw new \InvalidArgumentException(sprintf('The class "%s" does not exist.', $class));
        }

        return $this->classes[$class];
    }

    /**
     * Returns the info of a class.
     *
     * @param string $class The class.
     *
     * @return array The info of the class.
     */
    public function getClassInfo($class)
    {
        if (!$this->hasClass($class)) {
            throw new \InvalidArgumentException(sprintf('The class "%s" does not exist.', $class));
        }

        if (null === $this->infoClass) {
            $infoClass = get_class($this).'Info';
            $this->infoClass = new $infoClass();
        }

        return $this->infoClass->{'get'.str_replace('\\', '', $class).'ClassInfo'}();
    }
}
