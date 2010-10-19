<?php

namespace BaseMap;

/**
 * Map of the Comment document.
 */
class Comment
{


    static protected $map = array (
  'fields' => 
  array (
    'name' => 
    array (
      'type' => 'string',
    ),
    'text' => 
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