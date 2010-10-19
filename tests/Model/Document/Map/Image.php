<?php

namespace BaseMap;

/**
 * Map of the Image document.
 */
class Image
{


    static protected $map = array (
  'fields' => 
  array (
    'name' => 
    array (
      'type' => 'string',
    ),
    'description' => 
    array (
      'type' => 'string',
    ),
    'file' => 
    array (
      'type' => 'raw',
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