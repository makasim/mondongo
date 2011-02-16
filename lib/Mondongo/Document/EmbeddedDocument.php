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
 * The base class for documents embeddeds.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class EmbeddedDocument
{
    protected $embeddedsChanged = array();

    /**
     * Returns the metadata info of the class.
     *
     * @return array The metadata info of the class.
     */
    static public function metadata()
    {
        return static::mondongo()->getMetadata()->getClassInfo(get_class($this));
    }

    /**
     * Returns if the document is modified.
     *
     * @return bool Returns if the document is modified.
     */
    public function isModified()
    {
        if ($this->getFieldsModified()) {
            return true;
        }

        foreach ($this->getEmbeddedsChanged() as $name => $embeddedChanged) {
            $embedded = $this->get($name);
            if (null === $embedded) {
                return true;
            }

            // one
            if ($embedded instanceof EmbeddedDocument) {
                if ($embedded->isModified()) {
                    return true;
                }
            // many
            } else {
                foreach ($embedded as $e) {
                    if ($e->isModified()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Clear modified.
     *
     * @return void
     */
    public function clearModified()
    {
        $this->clearFieldsModified();
        $this->clearEmbeddedsChanged();

        if (isset($this->data['embeddeds'])) {
            foreach ($this->data['embeddeds'] as $name => $embedded) {
                if (null !== $embedded) {
                    $this->setEmbeddedChanged($name, $embedded);
                }
            }
        }
    }

    /**
     * Revert the fields and embeddeds changed.
     */
    public function revertModified()
    {
        $this->revertFieldsModified();
        $this->revertEmbeddedsChanged();
    }

    /**
     * Returns if a field is modified.
     *
     * @param string $name The field name.
     *
     * @return bool If the field is modified.
     */
    public function isFieldModified($name)
    {
        return array_key_exists($name, $this->fieldsModified);
    }

    /**
     * Returns the old value of a field modified.
     *
     * @param string $name The field name.
     *
     * @return mixed The old value of a field modified.
     *
     * @throws \InvalidArgumentException If the field is not modified.
     */
    public function getFieldModified($name)
    {
        if (!$this->isFieldModified($name)) {
            throw new \InvalidArgumentException(sprintf('The field "%s" is not modified.', $name));
        }

        return $this->fieldsModified[$name];
    }

    /**
     * Set the old value of a field (internal).
     */
    public function setFieldModified($name, $value)
    {
        if (!array_key_exists($name, $this->data['fields'])) {
            throw new \InvalidArgumentException(sprintf('The field "%s" does not exist.', $name));
        }

        $this->fieldsModified[$name] = $value;
    }

    /**
     * Remove the old value of a field (internal).
     */
    public function removeFieldModified($name)
    {
        unset($this->fieldsModified[$name]);
    }

    /**
     * Returns the fields modified.
     *
     * @return array The fields modified.
     */
    public function getFieldsModified()
    {
        return $this->fieldsModified;
    }

    /**
     * Clear the fields modified.
     *
     * @return void
     */
    public function clearFieldsModified()
    {
        $this->fieldsModified = array();
    }

    /**
     * Revert the fields modified.
     *
     * @return void
     */
    public function revertFieldsModified()
    {
        foreach ($this->getFieldsModified() as $name => $value) {
            $this->data['fields'][$name] = $value;
        }
        $this->clearFieldsModified();
    }

    /**
     * Returns if an embedded is changed.
     *
     * @param string $name The embedded name.
     *
     * @return bool If an embedded is changed.
     */
    public function isEmbeddedChanged($name)
    {
        return array_key_exists($name, $this->embeddedsChanged);
    }

    /**
     * Returns the old embedded of an embedded (internal).
     *
     * @param string $name The embedded name.
     *
     * @return mixed The old embedded of an embedded.
     *
     * @throws \InvalidArgumentException If the embedded is not changed.
     */
    public function getEmbeddedChanged($name)
    {
        if (!$this->isEmbeddedChanged($name)) {
            throw new \InvalidArgumentException(sprintf('The embedded "%s" is not changed.', $name));
        }

        return $this->embeddedsChanged[$name];
    }

    /**
     * Set the old embedded of an embedded (internal).
     */
    public function setEmbeddedChanged($name, $value)
    {
        if (!array_key_exists($name, $this->data['embeddeds'])) {
            throw new \InvalidArgumentException(sprintf('The embedded "%s" does not exist.', $name));
        }

        if ($value instanceof \Mondongo\Group) {
            $value = $value->getElements();
        }

        $this->embeddedsChanged[$name] = $value;
    }

    /**
     * Remove the old embedded of an embedded (internal).
     */
    public function removeEmbeddedChanged($name)
    {
        unset($this->embeddedsChanged[$name]);
    }

    /**
     * Returns the old embedded of the embeddeds.
     *
     * @return array The old values of the embeddeds.
     */
    public function getEmbeddedsChanged()
    {
        return $this->embeddedsChanged;
    }

    /**
     * Clear the embeddeds changed.
     */
    public function clearEmbeddedsChanged()
    {
        if (isset($this->data['embeddeds'])) {
            foreach ($this->data['embeddeds'] as $name => $embedded) {
                if (null !== $embedded) {
                    if ($embedded instanceof EmbeddedDocument) {
                        $embedded->clearModified();
                    } else {
                        foreach ($embedded as $e) {
                            $e->clearModified();
                        }
                    }
                }
            }
        }
        $this->embeddedsChanged = array();
    }

    /**
     * Revert the embeddeds changed.
     */
    public function revertEmbeddedsChanged()
    {
        foreach ($this->getEmbeddedsChanged() as $name => $embedded) {
            $this->data['embeddeds'][$name] = $embedded;
        }
        $this->clearEmbeddedsChanged();
    }

    /**
     * Returns the document data.
     *
     * @return array The document data.
     */
    public function getDocumentData()
    {
        return $this->data;
    }

    /**
     * Returns the data to Mongo.
     *
     * @return array The data to Mongo.
     */
    public function dataToMongo()
    {
        $data = array();

        // fields
        if (isset($this->data['fields'])) {
            $fields = array();
            foreach ($this->data['fields'] as $name => $value) {
                if (null !== $value) {
                    $fields[$name] = $value;
                }
            }
            $data = array_merge($data, $this->fieldsToMongo($fields));
        }

        // embeddeds
        if (isset($this->data['embeddeds'])) {
            foreach ($this->data['embeddeds'] as $name => $embed) {
                if (null !== $embed) {
                    // one
                    if ($embed instanceof EmbeddedDocument) {
                        $data[$name] = $embed->dataToMongo();
                    // many
                    } else {
                        foreach ($embed as $key => $e) {
                            $data[$name][$key] = $e->dataToMongo();
                        }
                    }
                }
            }
        }

        return $data;
    }

    /*
     * Events.
     */
    public function preInsert()
    {
    }

    public function postInsert()
    {
    }

    public function preUpdate()
    {
    }

    public function postUpdate()
    {
    }

    public function preSave()
    {
    }

    public function postSave()
    {
    }

    public function preDelete()
    {
    }

    public function postDelete()
    {
    }
}
