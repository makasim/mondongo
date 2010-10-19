<?php

namespace BaseMap;

/**
 * Map of the AuthorTelephone document.
 */
class AuthorTelephone
{


    static protected $map = array (
  'fields' => 
  array (
    'number' => 
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
    'author' => 
    array (
      'class' => 'Model\\Document\\Author',
      'field' => 'telephone_id',
      'type' => 'one',
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