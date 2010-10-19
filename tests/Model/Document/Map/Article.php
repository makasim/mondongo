<?php

namespace BaseMap;

/**
 * Map of the Article document.
 */
class Article
{


    static protected $map = array (
  'fields' => 
  array (
    'title' => 
    array (
      'type' => 'string',
    ),
    'slug' => 
    array (
      'type' => 'string',
    ),
    'content' => 
    array (
      'type' => 'string',
    ),
    'is_active' => 
    array (
      'type' => 'boolean',
    ),
    'author_id' => 
    array (
      'type' => 'reference_one',
    ),
    'category_ids' => 
    array (
      'type' => 'reference_many',
    ),
  ),
  'references' => 
  array (
    'author' => 
    array (
      'class' => 'Model\\Document\\Author',
      'field' => 'author_id',
      'type' => 'one',
    ),
    'categories' => 
    array (
      'class' => 'Model\\Document\\Category',
      'field' => 'category_ids',
      'type' => 'many',
    ),
  ),
  'embeddeds' => 
  array (
    'source' => 
    array (
      'class' => 'Model\\Document\\Source',
      'type' => 'one',
    ),
    'comments' => 
    array (
      'class' => 'Model\\Document\\Comment',
      'type' => 'many',
    ),
  ),
  'relations' => 
  array (
    'summary' => 
    array (
      'class' => 'Model\\Document\\Summary',
      'field' => 'article_id',
      'type' => 'one',
    ),
    'news' => 
    array (
      'class' => 'Model\\Document\\News',
      'field' => 'article_id',
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