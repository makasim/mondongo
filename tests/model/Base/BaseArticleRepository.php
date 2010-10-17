<?php

/**
 * Base class of repository of Article document.
 */
abstract class BaseArticleRepository extends \Mondongo\Repository
{


    protected $documentClass = 'Article';


    protected $connectionName = NULL;


    protected $collectionName = 'article';


    protected $isFile = false;

    /**
     * Ensure indexes.
     *
     * @return void
     */
    public function ensureIndexes()
    {

    }
}