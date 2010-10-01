<?php

namespace Model\Repository\Base;

abstract class Source extends \Mondongo\Repository
{

    protected $documentClass = 'Model\\Document\\Source';

    protected $connectionName = NULL;

    protected $collectionName = 'source';
}