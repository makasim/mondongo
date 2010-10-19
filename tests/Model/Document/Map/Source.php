<?php

namespace BaseMap;

/**
 * Map of the Source document.
 */
class Source
{


    static protected $map = array (
  'fields' => 
  array (
    'name' => 
    array (
      'type' => 'string',
    ),
    'url' => 
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