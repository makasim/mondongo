<?php

namespace BaseMap;

/**
 * Map of the News document.
 */
class News
{


    static protected $map = array (
  'fields' => 
  array (
    'title' => 
    array (
      'type' => 'string',
    ),
    'article_id' => 
    array (
      'type' => 'reference_one',
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