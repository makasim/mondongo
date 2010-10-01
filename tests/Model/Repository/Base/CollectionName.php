<?php

namespace Model\Repository\Base;

abstract class CollectionName extends \Mondongo\Repository
{

    protected $documentClass = 'Model\\Document\\CollectionName';

    protected $connectionName = NULL;

    protected $collectionName = 'my_name';
}