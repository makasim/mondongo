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
use Mondongo\Mondator\Output\Output;
use Mondongo\Inflector;

/**
 * The Mondongo CoreStart extension.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class CoreStart extends Extension
{
    /**
     * @inheritdoc
     */
    protected function setup()
    {
        $this->addOptions(array(
            'default_document_output'      => null,
            'default_repository_output'    => null,
            'default_document_namespace'   => null,
            'default_repository_namespace' => null,
        ));
    }

    /**
     * @inheritdoc
     */
    protected function doProcess()
    {
        $this->processInitDefinitionsAndOutputs();

        if (!$this->configClass['is_embedded']) {
            $this->processDocumentGetMondongoMethod();
            $this->processDocumentGetRepositoryMethod();
            $this->processInitConnectionName();
            $this->processInitCollectionName();
            $this->processInitIndexes();
        }

        $this->processInitFields();
        $this->processInitReferences();
        $this->processInitEmbeddeds();

        if (!$this->configClass['is_embedded']) {
            $this->processInitRelations();
        }

        $this->processInitExtensionsEvents();
    }

    /*
     * Init Definitions and Outputs.
     */
    protected function processInitDefinitionsAndOutputs()
    {
        /*
         * Embed
         */
        $this->configClass['is_embedded'] = isset($this->configClass['is_embedded']) ? (bool) $this->configClass['is_embedded'] : false;

        /*
         * Namespaces
         */
        // init
        if (!isset($this->configClass['namespaces'])) {
            $this->configClass['namespaces'] = array('document' => null, 'repository' => null);
        }

        // default
        if (
          !isset($this->configClass['namespaces']['document'])
          &&
          $defaultDocumentNamespace = $this->getOption('default_document_namespace')
        ) {
            $this->configClass['namespaces']['document'] = $defaultDocumentNamespace;
        }

        if (
          !isset($this->configClass['namespaces']['repository'])
          &&
          $defaultDocumentNamespace = $this->getOption('default_repository_namespace')
        ) {
            $this->configClass['namespaces']['repository'] = $defaultDocumentNamespace;
        }

        // document
        if (isset($this->configClass['namespaces']['document'])) {
            $documentBaseClass = '\\'.$this->configClass['namespaces']['document'].'\\Base\\'.$this->className;
        } else {
            $this->configClass['namespaces']['document'] = null;
            $documentBaseClass = 'Base'.$this->className;
        }

        // repository
        if (!$this->configClass['is_embedded']) {
            if (isset($this->configClass['namespaces']['repository'])) {
                $repositoryClass     = $this->className;
                $repositoryBaseClass = '\\'.$this->configClass['namespaces']['repository'].'\\Base\\'.$this->className;
            } else {
                $this->configClass['namespaces']['repository'] = null;

                $repositoryClass     = $this->className.'Repository';
                $repositoryBaseClass = 'Base'.$this->className.'Repository';
            }
        }

        /*
         * Definitions
         */

        // document
        $this->definitions['document'] = $definition = new Definition($this->className);
        $definition->setNamespace($this->configClass['namespaces']['document']);
        $definition->setParentClass($documentBaseClass);
        $definition->setDocComment(<<<EOF
/**
 * {$this->className} document.
 */
EOF
        );

        // document_base
        $this->definitions['document_base'] = $definition = new Definition($this->getClassName($documentBaseClass));
        $definition->setNamespace($this->getNamespace($documentBaseClass));
        $definition->setIsAbstract(true);
        if ($this->configClass['is_embedded']) {
            $definition->setParentClass('\\Mondongo\\Document\\EmbeddedDocument');
        } else {
            $definition->setParentClass('\\Mondongo\\Document\\Document');
        }
        $definition->setDocComment(<<<EOF
/**
 * Base class of {$this->className} document.
 */
EOF
        );

        if (!$this->configClass['is_embedded']) {
            // repository
            $this->definitions['repository'] = $definition = new Definition($repositoryClass);
            $definition->setNamespace($this->configClass['namespaces']['repository']);
            $definition->setParentClass($repositoryBaseClass);
            $definition->setDocComment(<<<EOF
/**
 * Repository of {$this->className} document.
 */
EOF
            );

            // repository_base
            $this->definitions['repository_base'] = $definition = new Definition($this->getClassName($repositoryBaseClass));
            $definition->setNamespace($this->getNamespace($repositoryBaseClass));
            $definition->setIsAbstract(true);
            $definition->setParentClass('\\Mondongo\\Repository');
            $definition->setDocComment(<<<EOF
/**
 * Base class of repository of {$this->className} document.
 */
EOF
            );
        }

        /*
         * Outputs
         */

        // document
        $dir = $this->getOption('default_document_output');
        if (isset($this->configClass['document_output'])) {
            $dir = $this->configClass['document_output'];
        }
        if (!$dir) {
            throw new \RuntimeException(sprintf('The document of the class "%s" does not have output.', $this->className));
        }

        $this->outputs['document'] = new Output($dir);

        // document_base
        $this->outputs['document_base'] = new Output($this->outputs['document']->getDir().'/Base', true);

        // repository
        $dir = $this->getOption('default_repository_output');
        if (isset($this->configClass['repository_output'])) {
            $dir = $this->configClass['repository_output'];
        }
        if (!$dir) {
            throw new \RuntimeException(sprintf('The repository of the class "%s" does not have output.', $this->className));
        }

        $this->outputs['repository'] = new Output($dir);

        // repository_base
        $this->outputs['repository_base'] = new Output($this->outputs['repository']->getDir().'/Base', true);
    }

    /*
     * Document "getMondongo" method.
     */
    public function processDocumentGetMondongoMethod()
    {
        $method = new Method('public', 'getMondongo', '', <<<EOF
        return \Mondongo\Container::getForDocumentClass('{$this->definitions['document']->getFullClass()}');
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns the Mondongo of the document.
     *
     * @return Mondongo\Mondongo The Mondongo of the document.
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * Document "getRepository" method.
     */
    public function processDocumentGetRepositoryMethod()
    {
        $method = new Method('public', 'getRepository', '', <<<EOF
        return \$this->getMondongo()->getRepository('{$this->definitions['document']->getFullClass()}');
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns the repository of the document.
     *
     * @return Mondongo\Repository The repository of the document.
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * Connection name.
     */
    protected function processInitConnectionName()
    {
        if (!isset($this->configClass['connection'])) {
            $this->configClass['connection'] = null;
        }
    }

    /*
     * Collection name.
     */
    protected function processInitCollectionName()
    {
        if (!isset($this->configClass['collection'])) {
            $this->configClass['collection'] = Inflector::underscore($this->className);
        }
    }

    /*
     * Init indexes.
     */
    protected function processInitIndexes()
    {
        if (!isset($this->configClass['indexes'])) {
            $this->configClass['indexes'] = array();
        }
    }

    /*
     * Init Fields.
     */
    protected function processInitFields()
    {
        if (!isset($this->configClass['fields'])) {
            $this->configClass['fields'] = array();
        }
    }

    /*
     * Init References.
     */
    protected function processInitReferences()
    {
        if (!isset($this->configClass['references'])) {
            $this->configClass['references'] = array();
        }
    }

    /*
     * Init Embeddeds.
     */
    protected function processInitEmbeddeds()
    {
        if (!isset($this->configClass['embeddeds'])) {
            $this->configClass['embeddeds'] = array();
        }
    }

    /*
     * Init relations.
     */
    protected function processInitRelations()
    {
        if (!isset($this->configClass['relations'])) {
            $this->configClass['relations'] = array();
        }
    }

    /*
     * Init extensions events.
     */
    protected function processInitExtensionsEvents()
    {
        $this->configClass['extensions_events'] = array(
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
