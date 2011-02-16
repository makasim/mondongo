<?php

class ModelMetadataInfo
{
    public function getArticleClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'article',
            'fields' => array(
                'title' => array(
                    'type' => 'string',
                ),
                'content' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }
}