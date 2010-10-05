<?php

abstract class BaseArticle extends \Mondongo\Document\Document
{

    protected $data = array (
  'fields' => 
  array (
    'title' => NULL,
    'content' => NULL,
  ),
);

    protected $fieldsModified = array (
);

    static protected $map = array (
  'title' => 'Title',
  'content' => 'Content',
);

    public function getMondongo()
    {
        return \Mondongo\Container::getForDocumentClass('Article');
    }

    public function getRepository()
    {
        return $this->getMondongo()->getRepository('Article');
    }

    static public function getMap()
    {
        return self::$map;
    }

    public function setDocumentData($data)
    {
        $this->id = $data['_id'];

        if (isset($data['title'])) {
            $this->data['fields']['title'] = (string) $data['title'];
        }
        if (isset($data['content'])) {
            $this->data['fields']['content'] = (string) $data['content'];
        }


        
    }

    public function fieldsToMongo($fields)
    {
        if (isset($fields['title'])) {
            $fields['title'] = (string) $fields['title'];
        }
        if (isset($fields['content'])) {
            $fields['content'] = (string) $fields['content'];
        }


        return $fields;
    }

    public function setTitle($value)
    {
        if (!array_key_exists('title', $this->fieldsModified)) {
            $this->fieldsModified['title'] = $this->data['fields']['title'];
        } elseif ($value === $this->fieldsModified['title']) {
            unset($this->fieldsModified['title']);
        }

        $this->data['fields']['title'] = $value;
    }

    public function getTitle()
    {
        return $this->data['fields']['title'];
    }

    public function setContent($value)
    {
        if (!array_key_exists('content', $this->fieldsModified)) {
            $this->fieldsModified['content'] = $this->data['fields']['content'];
        } elseif ($value === $this->fieldsModified['content']) {
            unset($this->fieldsModified['content']);
        }

        $this->data['fields']['content'] = $value;
    }

    public function getContent()
    {
        return $this->data['fields']['content'];
    }
}