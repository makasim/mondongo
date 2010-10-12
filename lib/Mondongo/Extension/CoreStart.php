<?php

/*
 * Copyright 2010 Pablo Díez Pascual <pablodip@gmail.com>
 *
 * This file is part of Mondongo.
 *
 * Mondongo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mondongo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Mondongo. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mondongo\Extension;

use Mondongo\Mondator\Definition\Container;
use Mondongo\Mondator\Definition\Definition;
use Mondongo\Mondator\Definition\Method;
use Mondongo\Mondator\Extension;
use Mondongo\Inflector;

/**
 * The Mondongo CoreStart extension.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class CoreStart extends Extension
{
    protected $options = array(
        'default_document_namespace'   => false,
        'default_repository_namespace' => false,
    );

    /**
     * @inheritdoc
     */
    protected function doProcess()
    {
        $this->processInitDefinitions();

        if (!$this->classData['embed']) {
            $this->processDocumentGetMondongoMethod();
            $this->processDocumentGetRepositoryMethod();
            $this->processInitConnectionName();
            $this->processInitCollectionName();
        }

        $this->processInitFields();
        $this->processInitReferences();
        $this->processInitEmbeds();

        if (!$this->classData['embeds']) {
            $this->processInitRelations();
        }

        $this->processInitExtensionsEvents();

        if (isset($this->classData['extensions'])) {
            $this->processExtensionsFromArray($this->classData['extensions']);
        }
    }

    /*
     * Init Definitions.
     */
    protected function processInitDefinitions()
    {
        /*
         * Embed
         */
        $this->classData['embed'] = isset($this->classData['embed']) ? (bool) $this->classData['embed'] : false;

        /*
         * Namespaces
         */
        // init
        if (!isset($this->classData['namespaces'])) {
            $this->classData['namespaces'] = array('document' => null, 'repository' => null);
        }

        // default
        if (
          !isset($this->classData['namespaces']['document'])
          &&
          $defaultDocumentNamespace = $this->getOption('default_document_namespace')
        ) {
            $this->classData['namespaces']['document'] = $defaultDocumentNamespace;
        }

        if (
          !isset($this->classData['namespaces']['repository'])
          &&
          $defaultDocumentNamespace = $this->getOption('default_repository_namespace')
        ) {
            $this->classData['namespaces']['repository'] = $defaultDocumentNamespace;
        }

        // document
        if (isset($this->classData['namespaces']['document'])) {
            $documentBaseClass = '\\'.$this->classData['namespaces']['document'].'\\Base\\'.$this->className;
        } else {
            $this->classData['namespaces']['document'] = null;
            $documentBaseClass = 'Base'.$this->className;
        }

        // repository
        if (!$this->classData['embed']) {
            if (isset($this->classData['namespaces']['repository'])) {
                $repositoryClass     = $this->className;
                $repositoryBaseClass = '\\'.$this->classData['namespaces']['repository'].'\\Base\\'.$this->className;
            } else {
                $this->classData['namespaces']['repository'] = null;

                $repositoryClass     = $this->className.'Repository';
                $repositoryBaseClass = 'Base'.$this->className.'Repository';
            }
        }

        /*
         * Definitions
         */
        // document
        $this->container['document'] = $definition = new Definition();
        $definition->setNamespace($this->classData['namespaces']['document']);
        $definition->setClassName($this->className);
        $definition->setParentClass($documentBaseClass);
        $definition->setPHPDoc(<<<EOF
/**
 * {$this->className} document.
 */
EOF
        );

        // document_base
        $this->container['document_base'] = $definition = new Definition();
        $definition->setNamespace($this->getNamespace($documentBaseClass));
        $definition->setIsAbstract(true);
        $definition->setClassName($this->getClassName($documentBaseClass));
        if ($this->classData['embed']) {
            $definition->setParentClass('\\Mondongo\\Document\\DocumentEmbed');
        } else {
            $definition->setParentClass('\\Mondongo\\Document\\Document');
        }
        $definition->setPHPDoc(<<<EOF
/**
 * Base class of {$this->className} document.
 */
EOF
        );

        if (!$this->classData['embed']) {
            // repository
            $this->container['repository'] = $definition = new Definition();
            $definition->setNamespace($this->classData['namespaces']['repository']);
            $definition->setClassName($repositoryClass);
            $definition->setParentClass($repositoryBaseClass);
            $definition->setPHPDoc(<<<EOF
/**
 * Repository of {$this->className} document.
 */
EOF
            );

            // repository_base
            $this->container['repository_base'] = $definition = new Definition();
            $definition->setNamespace($this->getNamespace($repositoryBaseClass));
            $definition->setIsAbstract(true);
            $definition->setClassName($this->getClassName($repositoryBaseClass));
            $definition->setParentClass('\\Mondongo\\Repository');
            $definition->setPHPDoc(<<<EOF
/**
 * Base class of repository of {$this->className} document.
 */
EOF
            );
        }
    }

    /*
     * Document "getMondongo" method.
     */
    public function processDocumentGetMondongoMethod()
    {
        $method = new Method('public', 'getMondongo', '', <<<EOF
        return \Mondongo\Container::getForDocumentClass('{$this->container['document']->getFullClass()}');
EOF
        );
        $method->setPHPDoc(<<<EOF
    /**
     * Returns the Mondongo of the document.
     *
     * @return Mondongo\Mondongo The Mondongo of the document.
     */
EOF
        );

        $this->container['document_base']->addMethod($method);
    }

    /*
     * Document "getRepository" method.
     */
    public function processDocumentGetRepositoryMethod()
    {
        $method = new Method('public', 'getRepository', '', <<<EOF
        return \$this->getMondongo()->getRepository('{$this->container['document']->getFullClass()}');
EOF
        );
        $method->setPHPDoc(<<<EOF
    /**
     * Returns the repository of the document.
     *
     * @return Mondongo\Repository The repository of the document.
     */
EOF
        );

        $this->container['document_base']->addMethod($method);
    }

    /*
     * Connection name.
     */
    protected function processInitConnectionName()
    {
        if (!isset($this->classData['connection'])) {
            $this->classData['connection'] = null;
        }
    }

    /*
     * Collection name.
     */
    protected function processInitCollectionName()
    {
        if (!isset($this->classData['collection'])) {
            $this->classData['collection'] = Inflector::underscore($this->className);
        }
    }

    /*
     * Init Fields.
     */
    protected function processInitFields()
    {
        if (!isset($this->classData['fields'])) {
            $this->classData['fields'] = array();
        }
    }

    /*
     * Init References.
     */
    protected function processInitReferences()
    {
        if (!isset($this->classData['references'])) {
            $this->classData['references'] = array();
        }
    }

    /*
     * Init Embeds.
     */
    protected function processInitEmbeds()
    {
        if (!isset($this->classData['embeds'])) {
            $this->classData['embeds'] = array();
        }
    }

    /*
     * Init relations.
     */
    protected function processInitRelations()
    {
        if (!isset($this->classData['relations'])) {
            $this->classData['relations'] = array();
        }
    }

    /*
     * Init extensions events.
     */
    protected function processInitExtensionsEvents()
    {
        $this->classData['extensions_events'] = array(
            'preInsert'  => array(),
            'postInsert' => array(),
            'preUpdate'  => array(),
            'postUpdate' => array(),
            'preSave'    => array(),
            'postSave'   => array(),
            'preDelete'  => array(),
            'postDelete' => array(),
        );
    }
}
