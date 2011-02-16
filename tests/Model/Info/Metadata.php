<?php

namespace Model\Info;

class Metadata extends \Mondongo\Metadata
{
    protected $classes = array(
        'Model\\Author' => false,
        'Model\\AuthorTelephone' => false,
        'Model\\Category' => false,
        'Model\\Comment' => true,
        'Model\\Source' => true,
        'Model\\Article' => false,
        'Model\\ArticleVote' => false,
        'Model\\News' => false,
        'Model\\Summary' => false,
        'Model\\User' => false,
        'Model\\Message' => false,
        'Model\\Image' => false,
        'Model\\ConnectionGlobal' => false,
        'Model\\CollectionName' => false,
        'Model\\Events' => false,
        'Model\\EmbedNot' => true,
        'Model\\MultipleEmbeds' => false,
        'Model\\MultipleEmbedsEmbedded1' => false,
        'Model\\MultipleEmbedsEmbedded2' => false,
        'Model\\CustomMondongo' => false,
    );
}