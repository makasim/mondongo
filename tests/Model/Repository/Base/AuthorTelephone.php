<?php

namespace Model\Repository\Base;

abstract class AuthorTelephone extends \Mondongo\Repository
{

    protected $documentClass = 'Model\\Document\\AuthorTelephone';

    protected $connectionName = NULL;

    protected $collectionName = 'author_telephone';
}