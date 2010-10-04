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
use \Mondongo\Mondator\Output;

// namespaced
$classes = array(
    'Author' => array(
        'fields' => array(
            'name'         => 'string',
            'telephone_id' => 'raw',
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
            'content'      => 'string',
            'is_active'    => 'boolean',
            'author_id'    => 'raw',
            'category_ids' => 'raw',
        ),
        'references' => array(
            'author'     => array('class' => 'Model\Document\Author', 'field' => 'author_id', 'type' => 'one'),
            'categories' => array('class' => 'Model\Document\Category', 'field' => 'category_ids', 'type' => 'many'),
        ),
        'embeds' => array(
            'source'   => array('class' => 'Model\Document\Source', 'type' => 'one'),
            'comments' => array('class' => 'Model\Document\Comment', 'type' => 'many'),
        ),
    ),
    'User' => array(
        'fields' => array(
            'username'  => 'string',
            'is_active' => array('type' => 'boolean', 'default' => true),
        ),
    ),
    'ConnectionGlobal' => array(
        'connection' => 'global',
    ),
    'CollectionName' => array(
        'collection' => 'my_name',
    ),
);
foreach ($classes as &$class) {
    $class['namespaces'] = array(
        'document'   => 'Model\Document',
        'repository' => 'Model\Repository',
    );
}

$mondator = new Mondator();
$mondator->setClasses($classes);
$mondator->setExtensions(array(
    new Mondongo\Extension\CoreStart(),
    new Mondongo\Extension\CoreEnd(),
));
$mondator->setOutputs(array(
    'document'        => new Output(__DIR__.'/Model/Document'),
    'document_base'   => new Output(__DIR__.'/Model/Document/Base', true),
    'repository'      => new Output(__DIR__.'/Model/Repository'),
    'repository_base' => new Output(__DIR__.'/Model/Repository/Base', true),
));
$mondator->process();

// not namespaced
$mondator = new Mondator();
$mondator->setClasses(array(
    'Article' => array(
        'fields' => array(
            'title'   => 'string',
            'content' => 'string',
        ),
    ),
));
$mondator->setExtensions(array(
    new Mondongo\Extension\CoreStart(),
    new Mondongo\Extension\CoreEnd(),
));
$mondator->setOutputs(array(
    'document'        => new Output(__DIR__.'/model'),
    'document_base'   => new Output(__DIR__.'/model/base', true),
    'repository'      => new Output(__DIR__.'/model'),
    'repository_base' => new Output(__DIR__.'/model/base', true),
));
$mondator->process();

foreach (array(__DIR__.'/model/base', __DIR__.'/model') as $dir) {
    foreach (new DirectoryIterator($dir) as $file) {
        if ($file->isFile()) {
            require_once($file->getPathname());
        }
    }
}
