<?php

namespace BaseMap;

/**
 * Map of the Author document.
 */
class Author
{


    static protected $map = array (
  'fields' => 
  array (
    'name' => 
    array (
      'type' => 'string',
    ),
    'telephone_id' => 
    array (
      'type' => 'reference_one',
    ),
  ),
  'references' => 
  array (
    'telephone' => 
    array (
      'class' => 'Model\\Document\\AuthorTelephone',
      'field' => 'telephone_id',
      'type' => 'one',
    ),
  ),
  'embeddeds' => 
  array (
  ),
  'relations' => 
  array (
    'articles' => 
    array (
      'class' => 'Model\\Document\\Article',
      'field' => 'author_id',
      'type' => 'many',
    ),
  ),
);

    /**
     * Returns the map.
     *
     * @return array The data map.
     */
    static public function getMap()
    {
        return self::$map;
    }
}