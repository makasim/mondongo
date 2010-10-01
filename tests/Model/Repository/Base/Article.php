<?php

namespace Model\Repository\Base;

abstract class Article extends \Mondongo\Repository
{

    protected $documentClass = 'Model\\Document\\Article';

    protected $connectionName = NULL;

    protected $collectionName = 'article';
}