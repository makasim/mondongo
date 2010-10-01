<?php

abstract class BaseArticleRepository extends \Mondongo\Repository
{

    protected $documentClass = 'Article';

    protected $connectionName = NULL;

    protected $collectionName = 'article';
}