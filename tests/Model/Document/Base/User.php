<?php

namespace Model\Document\Base;

abstract class User extends \Mondongo\Document\Document
{

    protected $data = array (
  'fields' => 
  array (
    'username' => NULL,
    'is_active' => true,
  ),
);

    protected $fieldsModified = array (
  'is_active' => NULL,
);

    static protected $map = array (
  'username' => 'Username',
  'is_active' => 'IsActive',
);

    public function getMondongo()
    {
        return \Mondongo\Container::getForDocumentClass('Model\Document\User');
    }

    public function getRepository()
    {
        return $this->getMondongo()->getRepository('Model\Document\User');
    }

    static public function getMap()
    {
        return self::$map;
    }

    public function setDocumentData($data)
    {
        $this->id = $data['_id'];

        if (isset($data['username'])) {
            $this->data['fields']['username'] = (string) $data['username'];
        }
        if (isset($data['is_active'])) {
            $this->data['fields']['is_active'] = (bool) $data['is_active'];
        }


        $this->fieldsModified = array();
    }

    public function fieldsToMongo($fields)
    {
        if (isset($fields['username'])) {
            $fields['username'] = (string) $fields['username'];
        }
        if (isset($fields['is_active'])) {
            $fields['is_active'] = (bool) $fields['is_active'];
        }


        return $fields;
    }

    public function setUsername($value)
    {
        if (!array_key_exists('username', $this->fieldsModified)) {
            $this->fieldsModified['username'] = $this->data['fields']['username'];
        } elseif ($value === $this->fieldsModified['username']) {
            unset($this->fieldsModified['username']);
        }

        $this->data['fields']['username'] = $value;
    }

    public function getUsername()
    {
        return $this->data['fields']['username'];
    }

    public function setIsActive($value)
    {
        if (!array_key_exists('is_active', $this->fieldsModified)) {
            $this->fieldsModified['is_active'] = $this->data['fields']['is_active'];
        } elseif ($value === $this->fieldsModified['is_active']) {
            unset($this->fieldsModified['is_active']);
        }

        $this->data['fields']['is_active'] = $value;
    }

    public function getIsActive()
    {
        return $this->data['fields']['is_active'];
    }
}