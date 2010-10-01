<?php

namespace Model\Repository\Base;

abstract class ConnectionGlobal extends \Mondongo\Repository
{

    protected $documentClass = 'Model\\Document\\ConnectionGlobal';

    protected $connectionName = 'global';

    protected $collectionName = 'connection_global';
}