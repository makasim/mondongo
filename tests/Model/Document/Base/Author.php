<?php

namespace Model\Document\Base;

abstract class Author extends \Mondongo\Document\Document
{

    protected $data = array (
  'fields' => 
  array (
    'name' => NULL,
    'telephone_id' => NULL,
  ),
  'references' => 
  array (
    'telephone' => NULL,
  ),
  'relations' => 
  array (
    'articles' => NULL,
  ),
);

    protected $fieldsModified = array (
);

    public function getMondongo()
    {
        return \Mondongo\Container::getForDocumentClass('Model\Document\Author');
    }

    public function getRepository()
    {
        return $this->getMondongo()->getRepository('Model\Document\Author');
    }

    public function setDocumentData($data)
    {
        $this->id = $data['_id'];

        if (isset($data['name'])) {
            $this->data['fields']['name'] = (string) $data['name'];
        }
        if (isset($data['telephone_id'])) {
            $this->data['fields']['telephone_id'] = $data['telephone_id'];
        }


        
    }

    public function fieldsToMongo($fields)
    {
        if (isset($fields['name'])) {
            $fields['name'] = (string) $fields['name'];
        }
        if (isset($fields['telephone_id'])) {
            $fields['telephone_id'] = $fields['telephone_id'];
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

    public function setTelephoneId($value)
    {
        if (!array_key_exists('telephone_id', $this->fieldsModified)) {
            $this->fieldsModified['telephone_id'] = $this->data['fields']['telephone_id'];
        } elseif ($value === $this->fieldsModified['telephone_id']) {
            unset($this->fieldsModified['telephone_id']);
        }

        $this->data['fields']['telephone_id'] = $value;
    }

    public function getTelephoneId()
    {
        return $this->data['fields']['telephone_id'];
    }

    public function setTelephone($value)
    {
        if (!$value instanceof \Model\Document\AuthorTelephone) {
            throw new \InvalidArgumentException('The reference "telephone" is not an instance of "Model\Document\AuthorTelephone".');
        }
        if ($value->isNew()) {
            throw new \InvalidArgumentException('The reference "telephone" is new.');
        }

        $this->setTelephoneId($value->getId());
        $this->data['references']['telephone'] = $value;
    }

    public function getTelephone()
    {
        if (null === $this->data['references']['telephone']) {
            $value = \Mondongo\Container::getForDocumentClass('Model\Document\AuthorTelephone')->getRepository('Model\Document\AuthorTelephone')->get($this->getTelephoneId());
            if (!$value) {
                throw new \RuntimeException('The reference "telephone" does not exists');
            }
            $this->data['references']['telephone'] = $value;
        }

        return $this->data['references']['telephone'];
    }

    public function getArticles()
    {
        if (null === $this->data['relations']['articles']) {
            $this->data['relations']['articles'] = \Mondongo\Container::getForDocumentClass('Model\Document\Article')->getRepository('Model\Document\Article')->find(array(
                'query' => array('author_id' => $this->getId()),
            ));
        }

        return $this->data['relations']['articles'];
    }
}