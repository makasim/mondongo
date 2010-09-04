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

/**
 * Abstract class for documents.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class MondongoDocument extends MondongoDocumentBase
{
  protected $id;

  /**
   * Returns the Mondongo (using MondongoContainer).
   *
   * @return Mondongo The Mondongo.
   */
  public function getMondongo()
  {
    return MondongoContainer::getForName(get_class($this));
  }

  /**
   * Returns the Repository (using MondongoContainer).
   *
   * @return MondongoRepository The repository.
   */
  public function getRepository()
  {
    return $this->getMondongo()->getRepository(get_class($this));
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
   * Set the MongoId.
   *
   * @param MongoId The MongoId.
   *
   * @return void
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * Returns the MongoId.
   *
   * @return MongoId The MongoId.
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Save the document (using MondongoContainer).
   *
   * @return void
   */
  public function save()
  {
    $this->getRepository()->save($this);
  }

  /*
   * Delete the document (using MondongoContainer).
   *
   * @return void
   */
  public function delete()
  {
    $this->getRepository()->delete($this);
  }

  /**
   * Returns the query for save.
   *
   * @return array The query for save.
   */
  public function getQueryForSave()
  {
    $query = array();

    return $this->queryDocument($query, null, $this);
  }

  protected function queryDocument($query, $name, $document)
  {
    $definition = $document->getDefinition();
    $data       = $document->getDocumentData();

    // fields
    if ($fieldsModified = $document->getFieldsModified())
    {
      $fields = array();
      foreach (array_keys($fieldsModified) as $field)
      {
        if (null !== $value = $data['fields'][$field])
        {
          $fields[$field] = $value;
        }
      }

      if ($fields)
      {
        $closure = $definition->getClosureToMongo();
        $fields  = $closure($fields);
      }

      foreach (array_keys($fieldsModified) as $field)
      {
        // insert
        if ($this->isNew())
        {
          // base
          if (null === $name)
          {
            $query[$field] = $fields[$field];
          }
          // embed
          else
          {
            $q =& $query;
            foreach ($name as $n)
            {
              if (!isset($q[$n]))
              {
                $q[$n] = array();
              }
              $q =& $q[$n];
            }

            $q[$field] = $fields[$field];
          }
        }
        // update
        else
        {
          $fieldName = (null === $name ? '' : implode('.', $name).'.').$field;

          // set
          if (array_key_exists($field, $fields))
          {
            $query['$set'][$fieldName] = $fields[$field];
          }
          // unset
          else
          {
            $query['$unset'][$fieldName] = 1;
          }
        }
      }
    }

    // embeds
    if (isset($data['embeds']))
    {
      foreach ($data['embeds'] as $embedName => $embed)
      {
        if (null !== $embed)
        {
          $embedName = null === $name ? array($embedName) : array_merge($name, array($embedName));

          // one
          if ($embed instanceof MondongoDocumentEmbed)
          {
            $query = $this->queryDocument($query, $embedName, $embed);
          }
          // many
          else
          {
            $elements = $embed->getElements();

            // insert
            if ($this->isNew())
            {
              foreach ($elements as $key => $element)
              {
                $query = $this->queryDocument($query, array_merge($embedName, array($key)), $element);
              }
            }
            // update
            else
            {
              $originalElements = $embed->getOriginalElements();

              // insert
              foreach ($elements as $key => $element)
              {
                if (!isset($originalElements[$key]) || spl_object_hash($element) != spl_object_hash($originalElements[$key]))
                {
                  $query['$pushAll'][implode('.', $embedName)][] = $element->getDocumentDataToMongo();
                }
                // update
                else
                {
                  $query = $this->queryDocument($query, array_merge($embedName, array($key)), $element);
                }
              }

              // delete
              foreach ($originalElements as $key => $element)
              {
                if (!isset($elements[$key]) || spl_object_hash($element) != spl_object_hash($elements[$key]))
                {
                  $query['$pullAll'][implode('.', $embedName)][] = $element->getDocumentDataToMongo();
                }
              }
            }
          }
        }
      }
    }

    return $query;
  }

  /**
   * @see MondongoDocumentBaseSpeed
   */
  protected function hasDoGetMore($name)
  {
    return isset($this->data['relations']) ? array_key_exists($name, $this->data['relations']) : false;
  }

  /**
   * @see MondongoDocumentBaseSpeed
   */
  protected function doGetMore($name)
  {
    // relations
    if (isset($this->data['relations']) && array_key_exists($name, $this->data['relations']))
    {
      if (null === $this->data['relations'][$name])
      {
        $relation = $this->getDefinition()->getRelation($name);

        $class = $relation['class'];
        $field = $relation['field'];

        // one
        if ('one' == $relation['type'])
        {
          $value = MondongoContainer::getForName($class)->getRepository($class)->findOne(array($field => $this->getId()));
        }
        // many
        else
        {
          $value = MondongoContainer::getForName($class)->getRepository($class)->find(array($field => $this->getId()));
        }

        $this->data['relations'][$name] = $value;
      }

      return $this->data['relations'][$name];
    }
  }

  /**
   * @see MondongoDocumentBaseSpeed
   */
  protected function getMutators()
  {
    return array_merge(
      parent::getMutators(),
      isset($this->data['relations']) ? array_keys($this->data['relations']) : array()
    );
  }

  /**
   * __call
   *
   * @param string $name      The function name.
   * @param array  $arguments The arguments.
   *
   * @return mixed The return of the extension.
   *
   * @throws BadMethodCallException If the method does not exists.
   */
  public function __call($name, $arguments)
  {
    try
    {
      return parent::__call($name, $arguments);
    }
    catch (BadMethodCallException $e)
    {
    }

    foreach ($this->getDefinition()->getExtensions() as $extension)
    {
      if (method_exists($extension, $method = $name))
      {
        $extension->setInvoker($this);
        $retval = call_user_func_array(array($extension, $method), $arguments);
        $extension->clearInvoker();

        return $retval;
      }
    }

    throw new BadMethodCallException(sprintf('The method "%s" does not exists.', $name));
  }
}
