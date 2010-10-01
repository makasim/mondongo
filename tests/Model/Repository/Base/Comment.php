<?php

namespace Model\Repository\Base;

abstract class Comment extends \Mondongo\Repository
{

    protected $documentClass = 'Model\\Document\\Comment';

    protected $connectionName = NULL;

    protected $collectionName = 'comment';
}