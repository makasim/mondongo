<?php

// autoloader
require(__DIR__.'/../lib/vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php');

use Symfony\Component\ClassLoader\UniversalClassLoader;

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
            'name' => 'string',
        ),
        'references_one' => array(
            'telephone' => array('class' => 'Model\AuthorTelephone', 'field' => 'telephone_id'),
        ),
        'relations_many_one' => array(
            'articles' => array('class' => 'Model\Article'),
        ),
    ),
    'Model\AuthorTelephone' => array(
        'fields' => array(
            'number' => 'string',
        ),
        'relations_one' => array(
            'author' => array('class' => 'Model\Author', 'field' => 'telephone_id'),
        ),
    ),
    'Model\Category' => array(
        'fields' => array(
            'name' => 'string',
        ),
        'relations_many_many' => array(
            'articles' => array('class' => 'Model\Article'),
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
        ),
        'references_one' => array(
            'author' => array('class' => 'Model\Author'),
        ),
        'references_many' => array(
            'categories' => array('class' => 'Model\Category'),
        ),
        'embeddeds_one' => array(
            'source'   => array('class' => 'Model\Source'),
        ),
        'embeddeds_many' => array(
            'comments' => array('class' => 'Model\Comment'),
        ),
        'relations_one' => array(
            'summary' => array('class' => 'Model\Summary'),
        ),
        'relations_many_one' => array(
            'news' => array('class' => 'Model\News'),
        ),
        'relations_many_through' => array(
            'votes_users' => array('class' => 'Model\User', 'through' => 'Model\ArticleVote'),
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
    'Model\ArticleVote' => array(
        'references_one' => array(
            'article' => 'Model\Article',
            'user'    => 'Model\User',
        ),
    ),
    'Model\News' => array(
        'fields' => array(
            'title' => 'string',
        ),
        'references_one' => array(
            'article' => array('class' => 'Model\Article'),
        ),
    ),
    'Model\Summary' => array(
        'fields' => array(
            'text' => 'string',
        ),
        'references_one' => array(
            'article' => array('class' => 'Model\Article'),
        ),
    ),
    'Model\User' => array(
        'fields' => array(
            'username'  => 'string',
            'is_active' => array('type' => 'boolean', 'default' => true),
        ),
    ),
    'Model\Message' => array(
        'fields' => array(
            'author' => 'string',
            'text'   => 'string',
        ),
        'references_one' => array(
            'reply_to' => array('class' => 'Model\Message', 'field' => 'reply_to_id'),
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
        'relations_one' => array(
            'article' => array('class' => 'Model\Article'),
        ),
    ),
    'Model\MultipleEmbeds' => array(
        'fields' => array(
            'title'   => 'string',
            'content' => 'string',
        ),
        'embeddeds_many' => array(
            'embeddeds1' => array('class' => 'Model\MultipleEmbedsEmbedded1'),
        ),
    ),
    'Model\MultipleEmbedsEmbedded1' => array(
        'fields' => array(
            'name'    => 'string',
            'surname' => 'string',
        ),
        'embeddeds_many' => array(
            'embeddeds2' => array('class' => 'Model\MultipleEmbedsEmbedded2'),
        ),
    ),
    'Model\MultipleEmbedsEmbedded2' => array(
        'fields' => array(
            'field1' => 'string',
            'field2' => 'string',
        ),
    ),
    'Model\CustomMondongo' => array(
        'mondongo' => 'foobar',
        'fields' => array(
            'field' => 'string',
        ),
    ),
);

$mondator = new Mondator();
$mondator->setConfigClasses($configClasses);
$mondator->setExtensions(array(
    new Mondongo\Extension\Core(array(
        'metadata_class'  => 'Model\Info\Metadata',
        'metadata_output' => __DIR__.'/Model/Info',
        'default_output'  => __DIR__.'/Model',
    )),
    new Mondongo\Extension\DocumentArrayAccess(),
    new Mondongo\Extension\DocumentPropertyOverloading(),
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
        'metadata_class'  => 'ModelMetadata',
        'metadata_output' => __DIR__.'/model_no_namespaced/Info',
        'default_output'  => __DIR__.'/model_no_namespaced',
    )),
    new Mondongo\Extension\DocumentArrayAccess(),
    new Mondongo\Extension\DocumentPropertyOverloading(),
));
$mondator->process();

foreach (array(__DIR__.'/model_no_namespaced/Base', __DIR__.'/model_no_namespaced') as $dir) {
    foreach (new DirectoryIterator($dir) as $file) {
        if ($file->isFile()) {
            require_once($file->getPathname());
        }
    }
}
