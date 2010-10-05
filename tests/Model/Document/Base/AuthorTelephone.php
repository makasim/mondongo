<?php

namespace Model\Document\Base;

abstract class AuthorTelephone extends \Mondongo\Document\Document
{

    protected $data = array (
  'fields' => 
  array (
    'number' => NULL,
  ),
  'relations' => 
  array (
    'author' => NULL,
  ),
);

    protected $fieldsModified = array (
);

    static protected $map = array (
  'number' => 'Number',
  'author' => 'Author',
);

    public function getMondongo()
    {
        return \Mondongo\Container::getForDocumentClass('Model\Document\AuthorTelephone');
    }

    public function getRepository()
    {
        return $this->getMondongo()->getRepository('Model\Document\AuthorTelephone');
    }

    static public function getMap()
    {
        return self::$map;
    }

    public function setDocumentData($data)
    {
        $this->id = $data['_id'];

        if (isset($data['number'])) {
            $this->data['fields']['number'] = (string) $data['number'];
        }


        
    }

    public function fieldsToMongo($fields)
    {
        if (isset($fields['number'])) {
            $fields['number'] = (string) $fields['number'];
        }


        return $fields;
    }

    public function setNumber($value)
    {
        if (!array_key_exists('number', $this->fieldsModified)) {
            $this->fieldsModified['number'] = $this->data['fields']['number'];
        } elseif ($value === $this->fieldsModified['number']) {
            unset($this->fieldsModified['number']);
        }

        $this->data['fields']['number'] = $value;
    }

    public function getNumber()
    {
        return $this->data['fields']['number'];
    }

    public function getAuthor()
    {
        if (null === $this->data['relations']['author']) {
            $this->data['relations']['author'] = \Mondongo\Container::getForDocumentClass('Model\Document\Author')->getRepository('Model\Document\Author')->find(array(
                'query' => array('telephone_id' => $this->getId()),
                'one'   => true,
            ));
        }

        return $this->data['relations']['author'];
    }
}