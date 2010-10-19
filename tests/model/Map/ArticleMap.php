<?php

/**
 * Map of the Article document.
 */
class ArticleMap
{


    static protected $map = array (
  'fields' => 
  array (
    'title' => 
    array (
      'type' => 'string',
    ),
    'content' => 
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