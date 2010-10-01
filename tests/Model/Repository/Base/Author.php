<?php

namespace Model\Repository\Base;

abstract class Author extends \Mondongo\Repository
{

    protected $documentClass = 'Model\\Document\\Author';

    protected $connectionName = NULL;

    protected $collectionName = 'author';
}