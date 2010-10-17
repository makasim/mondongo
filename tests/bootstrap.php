<?php

// autoloader
require(__DIR__.'/../lib/vendor/symfony/src/Symfony/Component/HttpFoundation/UniversalClassLoader.php');

use Symfony\Component\HttpFoundation\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Mondongo\\Tests' => __DIR__,
    'Mondongo'        => __DIR__.'/../lib',
    'Model'           => __DIR__,
));
$loader->register();

/*
 * Mondator
 */
use \Mondongo\Mondator\Mondator;
use \Mondongo\Mondator\Output\Output;

// namespaced
$configClasses = array(
    'Author' => array(
        'fields' => array(
            'name'         => 'string',
            'telephone_id' => 'reference_one',
        ),
        'references' => array(
            'telephone' => array('class' => 'Model\Document\AuthorTelephone', 'field' => 'telephone_id', 'type' => 'one'),
        ),
        'relations' => array(
            'articles' => array('class' => 'Model\Document\Article', 'field' => 'author_id', 'type' => 'many'),
        ),
    ),
    'AuthorTelephone' => array(
        'fields' => array(
            'number' => 'string',
        ),
        'relations' => array(
            'author' => array('class' => 'Model\Document\Author', 'field' => 'telephone_id', 'type' => 'one'),
        ),
    ),
    'Category' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'relations' => array(
            'articles' => array('class' => 'Model\Document\Article', 'field' => 'category_ids', 'type' => 'many'),
        ),
    ),
    'Comment' => array(
        'embed' => true,
        'fields' => array(
            'name' => 'string',
            'text' => 'string',
        ),
    ),
    'Source' => array(
        'embed' => true,
        'fields' => array(
            'name' => 'string',
            'url'  => 'string',
        ),
    ),
    'Article' => array(
        'collection' => 'article',
        'fields' => array(
            'title'        => 'string',
            'slug'         => 'string',
            'content'      => 'string',
            'is_active'    => 'boolean',
            'author_id'    => 'reference_one',
            'category_ids' => 'reference_many',
        ),
        'references' => array(
            'author'     => array('class' => 'Model\Document\Author', 'field' => 'author_id', 'type' => 'one'),
            'categories' => array('class' => 'Model\Document\Category', 'field' => 'category_ids', 'type' => 'many'),
        ),
        'embeds' => array(
            'source'   => array('class' => 'Model\Document\Source', 'type' => 'one'),
            'comments' => array('class' => 'Model\Document\Comment', 'type' => 'many'),
        ),
        'relations' => array(
            'summary' => array('class' => 'Model\Document\Summary', 'field' => 'article_id', 'type' => 'one'),
            'news'    => array('class' => 'Model\Document\News', 'field' => 'article_id', 'type' => 'many'),
        ),
        'indexes' => array(
            array(
                'keys'    => array('slug' => 1),
                'options' => array('unique' => true),
            ),
            array(
                'keys' => array('author_id' => 1, 'is_active' => 1),
            ),
        ),
    ),
    'News' => array(
        'fields' => array(
            'title'      => 'string',
            'article_id' => 'reference_one',
        ),
        'references' => array(
            'article' => array('class' => 'Model\Document\Article', 'field' => 'article_id', 'type' => 'one'),
        ),
    ),
    'Summary' => array(
        'fields' => array(
            'article_id' => 'reference_one',
            'text'       => 'string',
        ),
        'references' => array(
            'article' => array('class' => 'Model\Document\Article', 'field' => 'article_id', 'type' => 'one'),
        ),
    ),
    'User' => array(
        'fields' => array(
            'username'  => 'string',
            'is_active' => array('type' => 'boolean', 'default' => true),
        ),
    ),
    'Image' => array(
        'is_file' => true,
        'fields'  => array(
            'name'        => 'string',
            'description' => 'string',
        ),
    ),
    'ConnectionGlobal' => array(
        'connection' => 'global',
    ),
    'CollectionName' => array(
        'collection' => 'my_name',
    ),
    'Events' => array(
        'fields' => array(
            'name' => 'string',
        ),
    ),
    'EmbedNot' => array(
        'embed' => true,
        'relations' => array(
            'article' => array('class' => 'Model\Document\Article', 'field' => 'embed_not_id', 'type' => 'one'),
        ),
    ),
);

$mondator = new Mondator();
$mondator->setConfigClasses($configClasses);
$mondator->setExtensions(array(
    new Mondongo\Extension\CoreStart(array(
        'default_document_namespace'   => 'Model\Document',
        'default_repository_namespace' => 'Model\Repository',
        'default_document_output'      => __DIR__.'/Model/Document',
        'default_repository_output'    => __DIR__.'/Model/Repository',
    )),
    new Mondongo\Extension\FromToArray(),
    new Mondongo\Extension\ArrayAccess(),
    new Mondongo\Extension\CoreEnd(),
));
$mondator->process();

// not namespaced
$mondator = new Mondator();
$mondator->setConfigClasses(array(
    'Article' => array(
        'fields' => array(
            'title'   => 'string',
            'content' => 'string',
        ),
    ),
));
$mondator->setExtensions(array(
    new Mondongo\Extension\CoreStart(array(
        'default_document_output'   => __DIR__.'/model',
        'default_repository_output' => __DIR__.'/model',
    )),
    new Mondongo\Extension\CoreEnd(),
));
$mondator->process();

foreach (array(__DIR__.'/model/base', __DIR__.'/model') as $dir) {
    foreach (new DirectoryIterator($dir) as $file) {
        if ($file->isFile()) {
            require_once($file->getPathname());
        }
    }
}
