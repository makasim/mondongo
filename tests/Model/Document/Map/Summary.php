<?php

namespace BaseMap;

/**
 * Map of the Summary document.
 */
class Summary
{


    static protected $map = array (
  'fields' => 
  array (
    'article_id' => 
    array (
      'type' => 'reference_one',
    ),
    'text' => 
    array (
      'type' => 'string',
    ),
  ),
  'references' => 
  array (
    'article' => 
    array (
      'class' => 'Model\\Document\\Article',
      'field' => 'article_id',
      'type' => 'one',
    ),
  ),
  'embeddeds' => 
  array (
  ),
  'relations' => 
  array (
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