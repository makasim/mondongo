<?php

namespace BaseMap;

/**
 * Map of the Category document.
 */
class Category
{


    static protected $map = array (
  'fields' => 
  array (
    'name' => 
    array (
      'type' => 'string',
    ),
  ),
  'references' => 
  array (
  ),
  'embeddeds' => 
  array (
  ),
  'relations' => 
  array (
    'articles' => 
    array (
      'class' => 'Model\\Document\\Article',
      'field' => 'category_ids',
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