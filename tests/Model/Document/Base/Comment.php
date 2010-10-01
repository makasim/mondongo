<?php

namespace Model\Document\Base;

abstract class Comment extends \Mondongo\Document\DocumentEmbed
{

    protected $data = array (
  'fields' => 
  array (
    'name' => NULL,
    'text' => NULL,
  ),
);

    protected $fieldsModified = array (
);

    public function getMondongo()
    {
        return \Mondongo\Container::getForDocumentClass('Model\Document\Comment');
    }

    public function getRepository()
    {
        return $this->getMondongo()->getRepository('Model\Document\Comment');
    }

    public function setDocumentData($data)
    {

        if (isset($data['name'])) {
            $this->data['fields']['name'] = (string) $data['name'];
        }
        if (isset($data['text'])) {
            $this->data['fields']['text'] = (string) $data['text'];
        }


        
    }

    public function fieldsToMongo($fields)
    {
        if (isset($fields['name'])) {
            $fields['name'] = (string) $fields['name'];
        }
        if (isset($fields['text'])) {
            $fields['text'] = (string) $fields['text'];
        }


        return $fields;
    }

    public function setName($value)
    {
        if (!array_key_exists('name', $this->fieldsModified)) {
            $this->fieldsModified['name'] = $this->data['fields']['name'];
        } elseif ($value === $this->fieldsModified['name']) {
            unset($this->fieldsModified['name']);
        }

        $this->data['fields']['name'] = $value;
    }

    public function getName()
    {
        return $this->data['fields']['name'];
    }

    public function setText($value)
    {
        if (!array_key_exists('text', $this->fieldsModified)) {
            $this->fieldsModified['text'] = $this->data['fields']['text'];
        } elseif ($value === $this->fieldsModified['text']) {
            unset($this->fieldsModified['text']);
        }

        $this->data['fields']['text'] = $value;
    }

    public function getText()
    {
        return $this->data['fields']['text'];
    }
}