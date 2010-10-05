<?php

namespace Model\Document\Base;

abstract class CollectionName extends \Mondongo\Document\Document
{

    protected $data = array (
);

    protected $fieldsModified = array (
);

    static protected $map = array (
);

    public function getMondongo()
    {
        return \Mondongo\Container::getForDocumentClass('Model\Document\CollectionName');
    }

    public function getRepository()
    {
        return $this->getMondongo()->getRepository('Model\Document\CollectionName');
    }

    static public function getMap()
    {
        return self::$map;
    }

    public function setDocumentData($data)
    {
        $this->id = $data['_id'];



        
    }

    public function fieldsToMongo($fields)
    {


        return $fields;
    }
}