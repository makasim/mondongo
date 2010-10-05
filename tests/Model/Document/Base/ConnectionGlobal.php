<?php

namespace Model\Document\Base;

abstract class ConnectionGlobal extends \Mondongo\Document\Document
{

    protected $data = array (
);

    protected $fieldsModified = array (
);

    static protected $map = array (
);

    public function getMondongo()
    {
        return \Mondongo\Container::getForDocumentClass('Model\Document\ConnectionGlobal');
    }

    public function getRepository()
    {
        return $this->getMondongo()->getRepository('Model\Document\ConnectionGlobal');
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