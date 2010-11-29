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
    'Model\Author' => array(
        'fields' => array(
            'name'         => 'string',
            'telephone_id' => 'reference_one',
        ),
        'references' => array(
            'telephone' => array('class' => 'Model\AuthorTelephone', 'field' => 'telephone_id', 'type' => 'one'),
        ),
        'relations' => array(
            'articles' => array('class' => 'Model\Article', 'field' => 'author_id', 'type' => 'many'),
        ),
    ),
    'Model\AuthorTelephone' => array(
        'fields' => array(
            'number' => 'string',
        ),
        'relations' => array(
            'author' => array('class' => 'Model\Author', 'field' => 'telephone_id', 'type' => 'one'),
        ),
    ),
    'Model\Category' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'relations' => array(
            'articles' => array('class' => 'Model\Article', 'field' => 'category_ids', 'type' => 'many'),
        ),
    ),
    'Model\Comment' => array(
        'is_embedded' => true,
        'fields' => array(
            'name' => 'string',
            'text' => 'string',
        ),
    ),
    'Model\Source' => array(
        'is_embedded' => true,
        'fields' => array(
            'name' => 'string',
            'url'  => 'string',
        ),
    ),
    'Model\Article' => array(
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
            'author'     => array('class' => 'Model\Author', 'field' => 'author_id', 'type' => 'one'),
            'categories' => array('class' => 'Model\Category', 'field' => 'category_ids', 'type' => 'many'),
        ),
        'embeddeds' => array(
            'source'   => array('class' => 'Model\Source', 'type' => 'one'),
            'comments' => array('class' => 'Model\Comment', 'type' => 'many'),
        ),
        'relations' => array(
            'summary' => array('class' => 'Model\Summary', 'field' => 'article_id', 'type' => 'one'),
            'news'    => array('class' => 'Model\News', 'field' => 'article_id', 'type' => 'many'),
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
    'Model\News' => array(
        'fields' => array(
            'title'      => 'string',
            'article_id' => 'reference_one',
        ),
        'references' => array(
            'article' => array('class' => 'Model\Article', 'field' => 'article_id', 'type' => 'one'),
        ),
    ),
    'Model\Summary' => array(
        'fields' => array(
            'article_id' => 'reference_one',
            'text'       => 'string',
        ),
        'references' => array(
            'article' => array('class' => 'Model\Article', 'field' => 'article_id', 'type' => 'one'),
        ),
    ),
    'Model\User' => array(
        'fields' => array(
            'username'  => 'string',
            'is_active' => array('type' => 'boolean', 'default' => true),
        ),
    ),
    'Model\Image' => array(
        'is_file'    => true,
        'collection' => 'image',
        'fields'  => array(
            'name'        => 'string',
            'description' => 'string',
        ),
    ),
    'Model\ConnectionGlobal' => array(
        'connection' => 'global',
    ),
    'Model\CollectionName' => array(
        'collection' => 'my_name',
    ),
    'Model\Events' => array(
        'fields' => array(
            'name' => 'string',
        ),
    ),
    'Model\EmbedNot' => array(
        'is_embedded' => true,
        'relations' => array(
            'article' => array('class' => 'Model\Article', 'field' => 'embed_not_id', 'type' => 'one'),
        ),
    ),
    'Model\MultipleEmbeds' => array(
        'fields' => array(
            'title'   => 'string',
            'content' => 'string',
        ),
        'embeddeds' => array(
            'embeddeds1' => array('class' => 'Model\MultipleEmbedsEmbedded1', 'type' => 'many'),
        ),
    ),
    'Model\MultipleEmbedsEmbedded1' => array(
        'fields' => array(
            'name'    => 'string',
            'surname' => 'string',
        ),
        'embeddeds' => array(
            'embeddeds2' => array('class' => 'Model\MultipleEmbedsEmbedded2', 'type' => 'many'),
        ),
    ),
    'Model\MultipleEmbedsEmbedded2' => array(
        'fields' => array(
            'field1' => 'string',
            'field2' => 'string',
        ),
    ),
);

$mondator = new Mondator();
$mondator->setConfigClasses($configClasses);
$mondator->setExtensions(array(
    new Mondongo\Extension\Core(array(
        'default_output' => __DIR__.'/Model',
    )),
    new Mondongo\Extension\DocumentDataCamelCaseMap(),
    new Mondongo\Extension\DocumentFromToArray(),
    new Mondongo\Extension\DocumentArrayAccess(),
    new Mondongo\Extension\DocumentPropertyOverloading(),
    new Mondongo\Extension\DocumentDataMap(),
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
    new Mondongo\Extension\Core(array(
        'default_output' => __DIR__.'/model',
    )),
    new Mondongo\Extension\DocumentDataCamelCaseMap(),
    new Mondongo\Extension\DocumentFromToArray(),
    new Mondongo\Extension\DocumentArrayAccess(),
    new Mondongo\Extension\DocumentPropertyOverloading(),
    new Mondongo\Extension\DocumentDataMap(),
));
$mondator->process();

foreach (array(__DIR__.'/model/Base', __DIR__.'/model') as $dir) {
    foreach (new DirectoryIterator($dir) as $file) {
        if ($file->isFile()) {
            require_once($file->getPathname());
        }
    }
}
