<?php

namespace Model\Document\Base;

abstract class Source extends \Mondongo\Document\DocumentEmbed
{

    protected $data = array (
  'fields' => 
  array (
    'name' => NULL,
    'url' => NULL,
  ),
);

    protected $fieldsModified = array (
);

    public function getMondongo()
    {
        return \Mondongo\Container::getForDocumentClass('Model\Document\Source');
    }

    public function getRepository()
    {
        return $this->getMondongo()->getRepository('Model\Document\Source');
    }

    public function setDocumentData($data)
    {

        if (isset($data['name'])) {
            $this->data['fields']['name'] = (string) $data['name'];
        }
        if (isset($data['url'])) {
            $this->data['fields']['url'] = (string) $data['url'];
        }


        
    }

    public function fieldsToMongo($fields)
    {
        if (isset($fields['name'])) {
            $fields['name'] = (string) $fields['name'];
        }
        if (isset($fields['url'])) {
            $fields['url'] = (string) $fields['url'];
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

    public function setUrl($value)
    {
        if (!array_key_exists('url', $this->fieldsModified)) {
            $this->fieldsModified['url'] = $this->data['fields']['url'];
        } elseif ($value === $this->fieldsModified['url']) {
            unset($this->fieldsModified['url']);
        }

        $this->data['fields']['url'] = $value;
    }

    public function getUrl()
    {
        return $this->data['fields']['url'];
    }
}