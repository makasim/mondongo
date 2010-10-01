<?php

namespace Model\Repository\Base;

abstract class User extends \Mondongo\Repository
{

    protected $documentClass = 'Model\\Document\\User';

    protected $connectionName = NULL;

    protected $collectionName = 'user';
}