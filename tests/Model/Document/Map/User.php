<?php

namespace BaseMap;

/**
 * Map of the User document.
 */
class User
{


    static protected $map = array (
  'fields' => 
  array (
    'username' => 
    array (
      'type' => 'string',
    ),
    'is_active' => 
    array (
      'type' => 'boolean',
      'default' => true,
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