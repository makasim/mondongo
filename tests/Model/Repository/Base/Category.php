<?php

namespace Model\Repository\Base;

abstract class Category extends \Mondongo\Repository
{

    protected $documentClass = 'Model\\Document\\Category';

    protected $connectionName = NULL;

    protected $collectionName = 'category';
}