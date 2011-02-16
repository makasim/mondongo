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

use Mondongo\Mondator\Extension;
use Mondongo\Mondator\Definition;
use Mondongo\Mondator\Definition\Method;
use Mondongo\Mondator\Definition\Property;
use Mondongo\Mondator\Output;
use Mondongo\Type\Container as TypeContainer;
use Mondongo\Inflector;

/**
 * The Mondongo Core extension.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Core extends Extension
{
    /**
     * {@inheritdoc}
     */
    protected function setup()
    {
        $this->addRequiredOptions(array(
            'metadata_class',
            'metadata_output',
        ));

        $this->addOptions(array(
            'default_output'    => null,
            'default_behaviors' => array(),
        ));
    }

    /**
     * @ineritdoc
     */
    public function getNewClassExtensions($class, \ArrayObject $configClass)
    {
        $classExtensions = array();

        // default behaviors
        foreach ($this->getOption('default_behaviors') as $behavior) {
            if (!empty($configClass['is_embedded']) && !empty($behavior['not_with_embeddeds'])) {
                continue;
            }
            $classExtensions[] = $this->createClassExtensionFromArray($behavior);
        }

        // behaviors
        if (isset($configClass['behaviors'])) {
            foreach ($configClass['behaviors'] as $behavior) {
                $classExtensions[] = $this->createClassExtensionFromArray($behavior);
            }
        }

        return $classExtensions;
    }

    /**
     * {@inheritdoc}
     */
    protected function doConfigClassProcess()
    {
        // is embedded
        if (isset($this->configClass['is_embedded'])) {
            $this->configClass['is_embedded'] = (bool) $this->configClass['is_embedded'];
        } else {
            $this->configClass['is_embedded'] = false;
        }

        // init
        $this->processInitFinalClass();

        if (!$this->configClass['is_embedded']) {
            $this->processInitMondongo();
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

        // is_file
        $this->processIsFile();
    }

    /**
     * {@inheritdoc}
     */
    protected function doClassProcess()
    {
        // parse and check
        $this->parseAndCheckFields();
        $this->parseAndCheckReferences();
        $this->parseAndCheckEmbeddeds();
        if (!$this->configClass['is_embedded']) {
            $this->parseAndCheckRelations();
        }
        $this->checkDataNames();

        // definitions
        $this->processInitDefinitions();

        // document
        if (!$this->configClass['is_embedded']) {
            $this->processDocumentMondongoMethod();
            $this->processDocumentRepositoryMethod();
        }

        $this->processDocumentDataProperty();
        $this->processDocumentFieldsModifiedsProperty();

        $this->processDocumentSetDocumentDataMethod();
        $this->processDocumentFieldsToMongoMethod();

        $this->processDocumentFields();
        $this->processDocumentReferences();
        $this->processDocumentEmbeddeds();
        if (!$this->configClass['is_embedded']) {
            $this->processDocumentRelations();
        }

        $this->processDocumentSetMethod();
        $this->processDocumentGetMethod();

        $this->processDocumentFromArrayMethod();
        $this->processDocumentToArrayMethod();

        $this->processDocumentExtensionsEventsMethods();

        // repository
        if (!$this->configClass['is_embedded']) {
            $this->processRepositoryDocumentClassProperty();
            $this->processRepositoryConnectionNameProperty();
            $this->processRepositoryCollectionNameProperty();
            $this->processRepositoryIsFileProperty();
            $this->processRepositoryEnsureIndexesMethod();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doPostGlobalProcess()
    {
        /*
         * Metadata.
         */
        $output = new Output($this->getOption('metadata_output'), true);
        $definition = new Definition($this->getOption('metadata_class'), $output);
        $definition->setParentClass('\Mondongo\Metadata');
        $this->definitions['metadata'] = $definition;

        $output = new Output($this->getOption('metadata_output'), true);
        $definition = new Definition($this->getOption('metadata_class').'Info', $output);
        $this->definitions['metadata_info'] = $definition;

        $classes = array();
        foreach ($this->configClasses as $class => $configClass) {
            $classes[$class] = $configClass['is_embedded'];

            $info = array();
            // general
            $info['is_embedded'] = $configClass['is_embedded'];
            if (!$info['is_embedded']) {
                $info['mondongo'] = $configClass['mondongo'];
                $info['connection'] = $configClass['connection'];
                $info['collection'] = $configClass['collection'];
            }
            // fields
            $info['fields'] = $configClass['fields'];
            // references
            $info['references_one'] = $configClass['references_one'];
            $info['references_many'] = $configClass['references_many'];
            // embeddeds
            $info['embeddeds_one'] = $configClass['embeddeds_one'];
            $info['embeddeds_many'] = $configClass['embeddeds_many'];
            // relations
            if (!$info['is_embedded']) {
                $info['relations_one'] = $configClass['relations_one'];
                $info['relations_many_one'] = $configClass['relations_many_one'];
                $info['relations_many_many'] = $configClass['relations_many_many'];
                $info['relations_many_through'] = $configClass['relations_many_through'];
            }
            // indexes
            if (!$info['is_embedded']) {
                $info['indexes'] = $configClass['indexes'];
            }

            $info = \Mondongo\Mondator\Dumper::exportArray($info, 12);

            $method = new Method('public', 'get'.str_replace('\\', '', $class).'ClassInfo', '', <<<EOF
        return $info;
EOF
            );
            $this->definitions['metadata_info']->addMethod($method);
        }

        $property = new Property('protected', 'classes', $classes);
        $this->definitions['metadata']->addProperty($property);
    }

    /*
     * Init Definitions.
     */
    protected function processInitDefinitions()
    {
        /*
         * Classes.
         */
        $classes = array('document' => $this->class);
        if (false !== $pos = strrpos($classes['document'], '\\')) {
            $documentNamespace = substr($classes['document'], 0, $pos);
            $documentClassName = substr($classes['document'], $pos + 1);
            $classes['document_base']   = $documentNamespace.'\\Base\\'.$documentClassName;
            $classes['repository']      = $documentNamespace.'\\'.$documentClassName.'Repository';
            $classes['repository_base'] = $documentNamespace.'\\Base\\'.$documentClassName.'Repository';
        } else {
            $classes['document_base']   = 'Base'.$classes['document'];
            $classes['repository']      = $classes['document'].'Repository';
            $classes['repository_base'] = 'Base'.$classes['document'].'Repository';
        }

        /*
         * Definitions
         */

        // document
        $dir = $this->getOption('default_output');
        if (isset($this->configClass['output'])) {
            $dir = $this->configClass['output'];
        }
        if (!$dir) {
            throw new \RuntimeException(sprintf('The document of the class "%s" does not have output.', $this->class));
        }
        $output = new Output($dir);

        $this->definitions['document'] = $definition = new Definition($classes['document'], $output);
        $definition->setParentClass('\\'.$classes['document_base']);
        $definition->setDocComment(<<<EOF
/**
 * {$this->class} document.
 */
EOF
        );

        // document_base
        $output = new Output($this->definitions['document']->getOutput()->getDir().'/Base', true);

        $this->definitions['document_base'] = $definition = new Definition($classes['document_base'], $output);
        $definition->setIsAbstract(true);
        if ($this->configClass['is_embedded']) {
            $definition->setParentClass('\Mondongo\Document\EmbeddedDocument');
        } else {
            $definition->setParentClass('\Mondongo\Document\Document');
        }
        $definition->setDocComment(<<<EOF
/**
 * Base class of {$this->class} document.
 */
EOF
        );

        if (!$this->configClass['is_embedded']) {
            // repository
            $dir = $this->getOption('default_output');
            if (isset($this->configClass['output'])) {
                $dir = $this->configClass['output'];
            }
            if (!$dir) {
                throw new \RuntimeException(sprintf('The repository of the class "%s" does not have output.', $this->class));
            }
            $output = new Output($dir);

            $this->definitions['repository'] = $definition = new Definition($classes['repository'], $output);
            $definition->setParentClass('\\'.$classes['repository_base']);
            $definition->setDocComment(<<<EOF
/**
 * Repository of {$this->class} document.
 */
EOF
            );

            // repository_base
            $output = new Output($this->definitions['repository']->getOutput()->getDir().'/Base', true);

            $this->definitions['repository_base'] = $definition = new Definition($classes['repository_base'], $output);
            $definition->setIsAbstract(true);
            $definition->setParentClass('\\Mondongo\\Repository');
            $definition->setDocComment(<<<EOF
/**
 * Base class of repository of {$this->class} document.
 */
EOF
            );
        }
    }

    /*
     * Final Class.
     */
    protected function processInitFinalClass()
    {
        if (!isset($this->configClass['final_class'])) {
            $this->configClass['final_class'] = $this->class;
        }
    }

    /*
     * Mondongo.
     */
    protected function processInitMondongo()
    {
        if (!isset($this->configClass['mondongo'])) {
            $this->configClass['mondongo'] = null;
        }
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
            $this->configClass['collection'] = str_replace('\\', '_', Inflector::underscore($this->class));
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
        if (!isset($this->configClass['references_one'])) {
            $this->configClass['references_one'] = array();
        }
        if (!isset($this->configClass['references_many'])) {
            $this->configClass['references_many'] = array();
        }
    }

    /*
     * Init Embeddeds.
     */
    protected function processInitEmbeddeds()
    {
        if (!isset($this->configClass['embeddeds_one'])) {
            $this->configClass['embeddeds_one'] = array();
        }
        if (!isset($this->configClass['embeddeds_many'])) {
            $this->configClass['embeddeds_many'] = array();
        }
    }

    /*
     * Init relations.
     */
    protected function processInitRelations()
    {
        if (!isset($this->configClass['relations_one'])) {
            $this->configClass['relations_one'] = array();
        }
        if (!isset($this->configClass['relations_many_one'])) {
            $this->configClass['relations_many_one'] = array();
        }
        if (!isset($this->configClass['relations_many_many'])) {
            $this->configClass['relations_many_many'] = array();
        }
        if (!isset($this->configClass['relations_many_through'])) {
            $this->configClass['relations_many_through'] = array();
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

    /*
     * Is File.
     */
    protected function processIsFile()
    {
        if (isset($this->configClass['is_file'])) {
            if (!is_bool($this->configClass['is_file'])) {
                throw new \RuntimeException(sprintf('The "is_file" option of the class "%s" is not boolean.', $this->class));
            }
            $this->configClass['fields']['file'] = 'raw';
        } else {
            $this->configClass['is_file'] = false;
        }
    }

    /*
     * Parse and Check.
     */
    protected function parseAndCheckFields()
    {
        foreach ($this->configClass['fields'] as $name => &$field) {
            if (is_string($field)) {
                $field = array('type' => $field);
            }
        }
        unset($field);

        foreach ($this->configClass['fields'] as $name => $field) {
            if (!is_array($field)) {
                throw new \RuntimeException(sprintf('The field "%s" of the class "%s" is not a string or array.', $name, $this->class));
            }
            if (!isset($field['type'])) {
                throw new \RuntimeException(sprintf('The field "%s" of the class "%s" does not have type.', $name, $this->class));
            }
            if (!TypeContainer::hasType($field['type'])) {
                throw new \RuntimeException(sprintf('The type "%s" of the field "%s" of the class "%s" does not exists.', $field['type'], $name, $this->class));
            }
        }
    }

    protected function parseAndCheckReferences()
    {
        // one
        foreach ($this->configClass['references_one'] as $name => &$reference) {
            $this->parseAndCheckAssociationClass($reference, $name);

            if (!isset($reference['field'])) {
                $reference['field'] = Inflector::fieldForClass($reference['class']);
            }
        }

        // many
        foreach ($this->configClass['references_many'] as $name => &$reference) {
            $this->parseAndCheckAssociationClass($reference, $name);

            if (!isset($reference['field'])) {
                $reference['field'] = Inflector::pluralFieldForClass($reference['class']);
            }
        }
    }

    protected function parseAndCheckEmbeddeds()
    {
        // one
        foreach ($this->configClass['embeddeds_one'] as $name => &$embedded) {
            $this->parseAndCheckAssociationClass($embedded, $name);
        }

        // many
        foreach ($this->configClass['embeddeds_many'] as $name => &$embedded) {
            $this->parseAndCheckAssociationClass($embedded, $name);
        }
    }

    protected function parseAndCheckRelations()
    {
        // one
        foreach ($this->configClass['relations_one'] as $name => &$relation) {
            $this->parseAndCheckAssociationClass($relation, $name);

            if (!isset($relation['field'])) {
                $relation['field'] = Inflector::fieldForClass($this->class);
            }
        }

        // many_one
        foreach ($this->configClass['relations_many_one'] as $name => &$relation) {
            $this->parseAndCheckAssociationClass($relation, $name);

            if (!isset($relation['field'])) {
                $relation['field'] = Inflector::fieldForClass($this->class);
            }
        }

        // many_many
        foreach ($this->configClass['relations_many_many'] as $name => &$relation) {
            $this->parseAndCheckAssociationClass($relation, $name);

            if (!isset($relation['field'])) {
                $relation['field'] = Inflector::pluralFieldForClass($this->class);
            }
        }

        // many_through
        foreach ($this->configClass['relations_many_through'] as $name => &$relation) {
            if (!is_array($relation)) {
                throw new \RuntimeException(sprintf('The relation_many_through "%s" of the class "%s" is not an array.', $name, $this->class));
            }
            if (!isset($relation['class'])) {
                throw new \RuntimeException(sprintf('The relation_many_through "%s" of the class "%s" does not have class.', $name, $this->class));
            }
            if (!isset($relation['through'])) {
                throw new \RuntimeException(sprintf('The relation_many_through "%s" of the class "%s" does not have through.', $name, $this->class));
            }

            if (!isset($relation['local'])) {
                $relation['local'] = Inflector::fieldForClass($this->class);
            }
            if (!isset($relation['foreign'])) {
                $relation['foreign'] = Inflector::fieldForClass($relation['class']);
            }
        }
    }

    protected function checkDataNames()
    {
        foreach (array_merge(
            array_keys($this->configClass['fields']),
            array_keys($this->configClass['references_one']),
            array_keys($this->configClass['references_many']),
            array_keys($this->configClass['embeddeds_one']),
            array_keys($this->configClass['embeddeds_many']),
            !$this->configClass['is_embedded'] ? array_keys($this->configClass['relations_one']) : array(),
            !$this->configClass['is_embedded'] ? array_keys($this->configClass['relations_many_one']) : array(),
            !$this->configClass['is_embedded'] ? array_keys($this->configClass['relations_many_many']) : array(),
            !$this->configClass['is_embedded'] ? array_keys($this->configClass['relations_many_through']) : array()
        ) as $name) {
            if (in_array($name, array('mondongo', 'repository', 'collection', 'id', 'query_for_save', 'fields_modified', 'document_data'))) {
                throw new \RuntimeException(sprintf('The document cannot be a data with the name "%s".', $name));
            }
        }
    }

    /*
     * Document "mondongo" method.
     */
    public function processDocumentMondongoMethod()
    {
        $mondongo = '';
        if ($this->configClass['mondongo']) {
            $mondongo = "'".$this->configClass['mondongo']."'";
        }

        $method = new Method('public', 'mondongo', '', <<<EOF
        return \Mondongo\Container::get($mondongo);
EOF
        );
        $method->setIsStatic(true);
        $method->setDocComment(<<<EOF
    /**
     * Returns the mondongo of the document.
     *
     * @return Mondongo\Mondongo The mondongo of the document.
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * Document "repository" method.
     */
    public function processDocumentRepositoryMethod()
    {
        $method = new Method('public', 'repository', '', <<<EOF
        return static::mondongo()->getRepository('{$this->configClass['final_class']}');
EOF
        );
        $method->setIsStatic(true);
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
     * Document "data" property.
     */
    protected function processDocumentDataProperty()
    {
        $data = array();

        // fields
        foreach ($this->fieldsToProcess() as $name => $field) {
            $data['fields'][$name] = isset($field['default']) ? $field['default'] : null;
        }

        // references
        foreach (array_merge(
            $this->configClass['references_one'],
            $this->configClass['references_many']
        ) as $name => $reference) {
            $data['references'][$name] = null;
        }

        // embeddeds
        foreach (array_merge(
            $this->configClass['embeddeds_one'],
            $this->configClass['embeddeds_many']
        ) as $name => $embed) {
            $data['embeddeds'][$name] = null;
        }

        // relations
        if (!$this->configClass['is_embedded']) {
            foreach (array_merge(
                $this->configClass['relations_one'],
                $this->configClass['relations_many_one'],
                $this->configClass['relations_many_many'],
                $this->configClass['relations_many_through']
            ) as $name => $relation) {
                $data['relations'][$name] = null;
            }
        }

        $property = new Property('protected', 'data', $data);

        $this->definitions['document_base']->addProperty($property);
    }

    /*
     * Document "fieldsModified" property.
     */
    protected function processDocumentFieldsModifiedsProperty()
    {
        $this->fieldsModified = array();
        foreach ($this->configClass['fields'] as $name => $field) {
            if (isset($field['default'])) {
                $this->fieldsModified[$name] = null;
            }
        }

        $property = new Property('protected', 'fieldsModified', $this->fieldsModified);

        $this->definitions['document_base']->addProperty($property);
    }

    /*
     * Document "setDocumentData" method.
     */
    protected function processDocumentSetDocumentDataMethod()
    {
        // _id
        $idCode = <<<EOF
        \$this->id = \$data['_id'];

EOF;
        if ($this->configClass['is_embedded']) {
            $idCode = '';
        }

        // fields
        $fieldsCode = '';
        foreach ($this->fieldsToProcess() as $name => $field) {
            $typeCode = str_replace("\n", "\n            ", strtr(TypeContainer::getType($field['type'])->toPHPInString(), array(
                '%from%' => "\$data['$name']",
                '%to%'   => "\$this->data['fields']['$name']",
            )));

            $fieldsCode .= <<<EOF
        if (isset(\$data['$name'])) {
            $typeCode
        }

EOF;
        }

        // embeddeds
        $embeddedsCode = '';
        foreach ($this->configClass['embeddeds_one'] as $name => $embedded) {
            $embeddedSetter = 'set'.Inflector::camelize($name);

            $embeddedsCode .= <<<EOF
        if (isset(\$data['$name'])) {
            \$embed = new \\{$embedded['class']}();
            \$embed->setDocumentData(\$data['$name']);
            \$this->$embeddedSetter(\$embed);
        }

EOF;
        }
        foreach ($this->configClass['embeddeds_many'] as $name => $embedded) {
            $embeddedSetter = 'set'.Inflector::camelize($name);

            $embeddedsCode .= <<<EOF
        if (isset(\$data['$name'])) {
            \$elements = array();
            foreach (\$data['$name'] as \$datum) {
                \$elements[] = \$element = new \\{$embedded['class']}();
                \$element->setDocumentData(\$datum);
            }
            \$group = new \Mondongo\Group(\$elements);
            \$this->$embeddedSetter(\$group);
        }

EOF;
        }

        $resetFieldsModified = $this->fieldsModified ? "\$this->fieldsModified = array();" : '';

        $method = new Method('public', 'setDocumentData', '$data', <<<EOF
$idCode
$fieldsCode
$embeddedsCode
        $resetFieldsModified
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Set the data in the document (hydrate).
     *
     * @return void
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * Document "fieldsToMongo" method.
     */
    public function processDocumentFieldsToMongoMethod()
    {
        $fieldsCode = '';
        foreach ($this->configClass['fields'] as $name => $field) {
            $typeCode = str_replace("\n", "\n            ", strtr(TypeContainer::getType($field['type'])->toMongoInString(), array(
                '%from%' => "\$fields['$name']",
                '%to%'   => "\$fields['$name']",
            )));

            $fieldsCode .= <<<EOF
        if (isset(\$fields['$name'])) {
            $typeCode
        }

EOF;
        }

        $method = new Method('public', 'fieldsToMongo', '$fields', <<<EOF
$fieldsCode

        return \$fields;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Convert an array of fields with data to Mongo values.
     *
     * @param array \$fields An array of fields with data.
     *
     * @return array The fields with data in Mongo values.
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * Document fields.
     */
    protected function processDocumentFields()
    {
        foreach ($this->fieldsToProcess() as $name => $field) {
            $referenceCode = '';
            if (isset($field['reference'])) {
                $referenceCode = <<<EOF
        \$this->data['references']['{$field['reference']}'] = null;
EOF;
            }

            // setter
            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', <<<EOF
        if (\$value === \$this->data['fields']['$name']) {
            return;
        }
        if (!\$this->isFieldModified('$name')) {
            \$this->setFieldModified('$name', \$this->data['fields']['$name']);
        } elseif (\$value === \$this->getFieldModified('$name')) {
            \$this->removeFieldModified('$name');
        }

        \$this->data['fields']['$name'] = \$value;

$referenceCode
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Set the "$name" field.
     *
     * @param mixed \$value The value.
     *
     * @return void
     */
EOF
            );

            $this->definitions['document_base']->addMethod($method);

            // getter
            $method = new Method('public', 'get'.Inflector::camelize($name), '', <<<EOF
        return \$this->data['fields']['$name'];
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the "$name" field.
     *
     * @return mixed The $name field.
     */
EOF
            );

            $this->definitions['document_base']->addMethod($method);
        }
    }

    /*
     * Document references.
     */
    protected function processDocumentReferences()
    {
        $saveReferencesCode = array();

        /*
         * one
         */
        foreach ($this->configClass['references_one'] as $name => $reference) {
            $fieldSetter = 'set'.Inflector::camelize($reference['field']);
            $fieldGetter = 'get'.Inflector::camelize($reference['field']);

            // setter
            $setterCode = <<<EOF
        if (null !== \$value && !\$value instanceof \\{$reference['class']}) {
            throw new \InvalidArgumentException('The reference "$name" is not an instance of "{$reference['class']}".');
        }
        if (null === \$value || \$value->isNew()) {
            \$this->{$fieldSetter}(null);
        } else if (null !== \$value) {
            \$this->{$fieldSetter}(\$value->getId());
        }

        \$this->data['references']['$name'] = \$value;
EOF;
            $setterDocComment = <<<EOF
    /**
     * Set the "$name" reference.
     *
     * @param {$reference['class']} \$value The value.
     */
EOF;

            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', $setterCode);
            $method->setDocComment($setterDocComment);
            $this->definitions['document_base']->addMethod($method);

            // getter
            $getterCode = <<<EOF
        if (null === \$this->data['references']['$name'] && null !== \$this->$fieldGetter()) {
            \$value = \\Mondongo\Container::get()
                ->getRepository('{$reference['class']}')
                ->findOneById(\$this->$fieldGetter())
            ;
            if (!\$value) {
                throw new \RuntimeException('The reference "$name" does not exists');
            }
            \$this->data['references']['$name'] = \$value;
        }

        return \$this->data['references']['$name'];
EOF;
            $getterDocComment = <<<EOF
    /**
     * Returns the "$name" reference.
     *
     * @return {$reference['class']} The "$name" reference.
     */
EOF;

            $method = new Method('public', 'get'.Inflector::camelize($name), '', $getterCode);
            $method->setDocComment($getterDocComment);
            $this->definitions['document_base']->addMethod($method);

            // save references
            $saveReferencesCode[] = <<<EOF
        \$reference = \$this->data['references']['$name'];
        if (null !== \$reference) {
            \$reference->save();
            if (!\$reference->isNew()) {
                \$this->$fieldSetter(\$reference->getId());
            }
        }
EOF;
        }

        /*
         * many
         */
        foreach ($this->configClass['references_many'] as $name => $reference) {
            $fieldSetter = 'set'.Inflector::camelize($reference['field']);
            $fieldGetter = 'get'.Inflector::camelize($reference['field']);

            $updateMethodName = 'update'.Inflector::camelize($name);

            // setter
            $setterCode = <<<EOF
        \$ids = array();
        if (null !== \$value) {
            if (!\$value instanceof \Mondongo\Group && !is_array(\$value)) {
                throw new \InvalidArgumentException('The reference "$name" is not an instance of Mondongo\Group or an array.');
            }
            if (is_array(\$value)) {
                \$value = new \Mondongo\Group(\$value);
            }
            \$value->setChangeCallback(array(\$this, '$updateMethodName'));

            foreach (\$value as \$document) {
                if (!\$document instanceof \\{$reference['class']}) {
                    throw new \InvalidArgumentException('Some document in the reference "$name" is not an instance of "{$reference['class']}".');
                }
                if (!\$document->isNew()) {
                    \$ids[] = \$document->getId();
                }
            }
        }

        \$this->{$fieldSetter}(count(\$ids) ? \$ids : null);
        \$this->data['references']['$name'] = \$value;
EOF;
            $setterDocComment = <<<EOF
    /**
     * Set the "$name" reference.
     *
     * @param Mondongo\Group \$value A Mondongo\Group instance.
     *
     * @return void
     */
EOF;

            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', $setterCode);
            $method->setDocComment($setterDocComment);
            $this->definitions['document_base']->addMethod($method);

            // getter
            $getterCode = <<<EOF
        if (null === \$this->data['references']['$name']) {
            \$group = new \Mondongo\Group();

            if (\$ids = \$this->$fieldGetter()) {
                \$value = \\Mondongo\Container::get()
                    ->getRepository('{$reference['class']}')
                    ->find(array('_id' => array('\$in' => \$ids)))
                ;
                if (!\$value || count(\$value) != count(\$ids)) {
                    throw new \RuntimeException('The reference "$name" does not exists');
                }

                \$group->setElements(\$value);
                \$group->setChangeCallback(array(\$this, '$updateMethodName'));
            }

            \$this->data['references']['$name'] = \$group;
        }

        return \$this->data['references']['$name'];
EOF;
            $getterDocComment = <<<EOF
    /**
     * Returns the "$name" reference.
     *
     * @return Mondongo\Group The "$name" reference.
     */
EOF;

            $method = new Method('public', 'get'.Inflector::camelize($name), '', $getterCode);
            $method->setDocComment($getterDocComment);
            $this->definitions['document_base']->addMethod($method);

            // save references
            $saveReferencesCode[] = <<<EOF
        if (\$this->data['references']['$name']) {
            \$ids = array();
            foreach (\$this->data['references']['$name'] as \$reference) {
                \$reference->save();
                if (\$reference->isNew()) {
                    continue;
                }
                \$ids[] = \$reference->getId();
            }
            if (count(\$ids)) {
                \$this->$fieldSetter(\$ids);
            }
        }
EOF;

            // update
            $updateCode = <<<EOF
        if (null !== \$this->data['references']['$name']) {
            \$ids = array();
            foreach (\$this->data['references']['$name'] as \$document) {
                if (!\$document instanceof \\{$reference['class']}) {
                    throw new \InvalidArgumentException('Some document of the "$name" reference is not an instance of "{$reference['class']}".');
                }
                if (!\$document->isNew()) {
                    \$ids[] = \$document->getId();
                }
            }

            if (\$ids !== \$this->$fieldGetter()) {
                \$this->$fieldSetter(\$ids);
            }
        }
EOF;
            $updateDocComment = <<<EOF
    /**
     * Update the "$name" reference.
     */
EOF;

            $method = new Method('public', 'update'.Inflector::camelize($name), '', $updateCode);
            $method->setDocComment($updateDocComment);
            $this->definitions['document_base']->addMethod($method);
        }

        // save references
        $method = new Method('public', 'saveReferences', '', implode("\n\n", $saveReferencesCode));
        $method->setDocComment(<<<EOF
    /**
     * Save the references.
     */
EOF
        );
        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * Document embeddeds.
     */
    protected function processDocumentEmbeddeds()
    {
        /*
         * one
         */
        foreach ($this->configClass['embeddeds_one'] as $name => $embedded) {
            // setter
            $setterCode = <<<EOF
        if (null !== \$value) {
            if (!\$value instanceof \\{$embedded['class']}) {
                throw new \InvalidArgumentException('The embed "$name" is not an instance of "{$embedded['class']}".');
            }
            if (null !== \$this->data['embeddeds']['$name'] && spl_object_hash(\$value) === spl_object_hash(\$this->data['embeddeds']['$name'])) {
                return;
            }
        } elseif (\$this->isEmbeddedChanged('$name') && null === \$this->getEmbeddedChanged('$name')) {
            \$this->removeEmbeddedChanged('$name');
            return;
        } elseif (null === \$this->data['embeddeds']['$name']) {
            return;
        }

        if (!\$this->isEmbeddedChanged('$name')) {
            \$this->setEmbeddedChanged('$name', \$this->data['embeddeds']['$name']);
        }

        \$this->data['embeddeds']['$name'] = \$value;
EOF;
            $setterDocComment = <<<EOF
    /**
     * Set the "$name" embed.
     *
     * @param {$embedded['class']} \$value The embed.
     *
     * @return void
     */
EOF;

            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', $setterCode);
            $method->setDocComment($setterDocComment);
            $this->definitions['document_base']->addMethod($method);

            // getter
            $getterCode = <<<EOF
        return \$this->data['embeddeds']['$name'];
EOF;
            $getterDocComment = <<<EOF
    /**
     * Returns the "$name" embed.
     *
     * @return {$embedded['class']} The "$name" embed.
     */
EOF;

            $method = new Method('public', 'get'.Inflector::camelize($name), '', $getterCode);
            $method->setDocComment($getterDocComment);
            $this->definitions['document_base']->addMethod($method);
        }

        /**
         * many
         */
        foreach ($this->configClass['embeddeds_many'] as $name => $embedded) {
            $updateMethodName = 'update'.Inflector::camelize($name);

            // setter
            $setterCode = <<<EOF
        if (null === \$value) {
            \$this->get('$name')->setElements(array());
            if (\$this->isEmbeddedChanged('$name') && null === \$this->getEmbeddedChanged('$name')) {
                \$this->removeEmbeddedChanged('$name');
            }
            return;
        }
        if (!\$value instanceof \Mondongo\Group && !is_array(\$value)) {
            throw new \InvalidArgumentException('The embed "$name" is not an instance of "Mondongo\Group" or an array.');
        }
        if (is_array(\$value)) {
            \$value = new \Mondongo\Group(\$value);
        } elseif (null !== \$this->data['embeddeds']['$name'] && \$this->data['embeddeds']['$name'] === \$value) {
            return;
        }
        \$value->setChangeCallback(array(\$this, '$updateMethodName'));

        \$this->data['embeddeds']['$name'] = \$value;
        \$this->$updateMethodName();
EOF;
            $setterDocComment = <<<EOF
    /**
     * Set the "$name" embed.
     *
     * @param Mondongo\Group \$value A Mondongo group.
     *
     * @return void
     */
EOF;

            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', $setterCode);
            $method->setDocComment($setterDocComment);
            $this->definitions['document_base']->addMethod($method);

            // getter
            $getterCode = <<<EOF
        if (null === \$this->data['embeddeds']['$name']) {
            \$this->data['embeddeds']['$name'] = \$group = new \\Mondongo\Group();
            \$group->setChangeCallback(array(\$this, '$updateMethodName'));
        }

        return \$this->data['embeddeds']['$name'];
EOF;
            $getterDocComment = <<<EOF
    /**
     * Returns the "$name" embed.
     *
     * @return Mondongo\Group The "$name" embed.
     */
EOF;

            $method = new Method('public', 'get'.Inflector::camelize($name), '', $getterCode);
            $method->setDocComment($getterDocComment);
            $this->definitions['document_base']->addMethod($method);

            // update
            $method = new Method('public', $updateMethodName, '', <<<EOF
        if (null !== \$this->data['embeddeds']['$name'] && count(\$this->data['embeddeds']['$name'])) {
            foreach (\$this->data['embeddeds']['$name'] as \$embedded) {
                if (!\$embedded instanceof \\{$embedded['class']}) {
                    throw new \InvalidArgumentException('Some document of the "$name" embedded is not an instance of "{$embedded['class']}".');
                }
            }
            if (!\$this->isEmbeddedChanged('$name')) {
                \$this->setEmbeddedChanged('$name', null);
            }
        } else {
            if (\$this->isEmbeddedChanged('$name') && null === \$this->getEmbeddedChanged('$name')) {
                \$this->removeEmbeddedChanged('$name');
            }
        }
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Update the "$name" embedded.
     *
     * @return void
     */
EOF
            );
            $this->definitions['document_base']->addMethod($method);
        }
    }

    /*
     * Document relations.
     */
    protected function processDocumentRelations()
    {
        /*
         * one
         */
        foreach ($this->configClass['relations_one'] as $name => $relation) {
            $getterCode = <<<EOF
        if (null === \$this->data['relations']['$name']) {
            \$this->data['relations']['$name'] = \Mondongo\Container::get()
                ->getRepository('{$relation['class']}')
                ->findOne(array('{$relation['field']}' => \$this->getId()))
            ;
        }

        return \$this->data['relations']['$name'];
EOF;
            $getterDocComment = <<<EOF
    /**
     * Returns the "$name" relation.
     *
     * @return {$relation['class']} The "$name" relation.
     */
EOF;

            $method = new Method('public', 'get'.Inflector::camelize($name), '', $getterCode);
            $method->setDocComment($getterDocComment);
            $this->definitions['document_base']->addMethod($method);
        }

        /*
         * many_one
         */
        foreach ($this->configClass['relations_many_one'] as $name => $relation) {
            $getterCode = <<<EOF
        if (null === \$this->data['relations']['$name']) {
            \$this->data['relations']['$name'] = \Mondongo\Container::get()
                ->getRepository('{$relation['class']}')
                ->find(array('{$relation['field']}' => \$this->getId()))
            ;
        }

        return \$this->data['relations']['$name'];
EOF;
            $getterDocComment = <<<EOF
    /**
     * Returns the "$name" relation.
     *
     * @return array The "$name" relation.
     */
EOF;

            $method = new Method('public', 'get'.Inflector::camelize($name), '', $getterCode);
            $method->setDocComment($getterDocComment);
            $this->definitions['document_base']->addMethod($method);
        }

        /*
         * many_many
         */
        foreach ($this->configClass['relations_many_many'] as $name => $relation) {
            $getterCode = <<<EOF
        if (null === \$this->data['relations']['$name']) {
            \$this->data['relations']['$name'] = \Mondongo\Container::get()
                ->getRepository('{$relation['class']}')
                ->find(array('{$relation['field']}' => \$this->getId()))
            ;
        }

        return \$this->data['relations']['$name'];
EOF;
            $getterDocComment = <<<EOF
    /**
     * Returns the "$name" relation.
     *
     * @return array The "$name" relation.
     */
EOF;

            $method = new Method('public', 'get'.Inflector::camelize($name), '', $getterCode);
            $method->setDocComment($getterDocComment);
            $this->definitions['document_base']->addMethod($method);
        }

        /*
         * many_through
         */
        foreach ($this->configClass['relations_many_through'] as $name => $relation) {
            $getterCode = <<<EOF
        if (null === \$this->data['relations']['$name']) {
            \$ids = array();
            foreach (\\{$relation['through']}::collection()
                ->find(array('{$relation['local']}' => \$this->getId()), array('{$relation['foreign']}' => 1))
            as \$value) {
                \$ids[] = \$value['{$relation['foreign']}'];
            }
            if (\$ids) {
                \$this->data['relations']['$name'] = \\{$relation['class']}::repository()
                    ->find(array('_id' => array('\$in' => \$ids)))
                ;
            }
        }

        return \$this->data['relations']['$name'];
EOF;
            $getterDocComment = <<<EOF
    /**
     * Returns the "$name" relation.
     *
     * @return array The "$name" relation.
     */
EOF;

            $method = new Method('public', 'get'.Inflector::camelize($name), '', $getterCode);
            $method->setDocComment($getterDocComment);
            $this->definitions['document_base']->addMethod($method);
        }
    }

    /*
     * Document "set" method.
     */
    protected function processDocumentSetMethod()
    {
        $code = '';
        // data
        foreach (array_merge(
            array_keys($this->fieldsToProcess()),
            array_keys($this->configClass['references_one']),
            array_keys($this->configClass['references_many']),
            array_keys($this->configClass['embeddeds_one']),
            array_keys($this->configClass['embeddeds_many'])
        ) as $name) {
            $setter = 'set'.Inflector::camelize($name);
            $code .= <<<EOF
        if ('$name' == \$name) {
            return \$this->$setter(\$value);
        }

EOF;
        }
        // exception
        $code .= <<<EOF

        throw new \InvalidArgumentException(sprintf('The data "%s" does not exists.', \$name));
EOF;

        $method = new Method('public', 'set', '$name, $value', $code);
        $method->setDocComment(<<<EOF
    /**
     * Set a data by name.
     *
     * @param string \$name  The data name.
     * @param mixed  \$value The value.
     *
     * @return void
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * Document "get" method.
     */
    protected function processDocumentGetMethod()
    {
        $code = '';
        // data
        foreach (array_merge(
            array_keys($this->configClass['fields']),
            array_keys($this->configClass['references_one']),
            array_keys($this->configClass['references_many']),
            array_keys($this->configClass['embeddeds_one']),
            array_keys($this->configClass['embeddeds_many']),
            !$this->configClass['is_embedded'] ? array_keys($this->configClass['relations_one']) : array(),
            !$this->configClass['is_embedded'] ? array_keys($this->configClass['relations_many_one']) : array(),
            !$this->configClass['is_embedded'] ? array_keys($this->configClass['relations_many_many']) : array(),
            !$this->configClass['is_embedded'] ? array_keys($this->configClass['relations_many_through']) : array()

        ) as $name) {
            $getter = 'get'.Inflector::camelize($name);
            $code .= <<<EOF
        if ('$name' == \$name) {
            return \$this->$getter();
        }

EOF;
        }
        // exception
        $code .= <<<EOF

        throw new \InvalidArgumentException(sprintf('The data "%s" does not exists.', \$name));
EOF;

        $method = new Method('public', 'get', '$name', $code);
        $method->setDocComment(<<<EOF
    /**
     * Get a data by name.
     *
     * @param string \$name  The data name.
     *
     * @return mixed The data value.
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * document "fromArray" method.
     */
    protected function processDocumentFromArrayMethod()
    {
        $code = '';

        // fields
        foreach ($this->fieldsToProcess() as $name => $field) {
            $code .= <<<EOF
        if (array_key_exists('$name', \$array)) {
            \$this->set('$name', \$array['$name']);
        }

EOF;
        }

        // references
        foreach ($this->configClass['references_one'] as $name => $reference) {
            $code .= <<<EOF
        if (array_key_exists('$name', \$array)) {
            \$this->set('$name', \$array['$name']);
        }

EOF;
        }
        foreach ($this->configClass['references_many'] as $name => $reference) {
            $code .= <<<EOF
        if (array_key_exists('$name', \$array)) {
            \$reference = \$array['$name'];
            if (is_array(\$reference)) {
                \$reference = new \Mondongo\Group(\$reference);
            }
            \$this->set('$name', \$reference);
        }

EOF;
        }

        // embeddeds
        foreach ($this->configClass['embeddeds_one'] as $name => $embedded) {
            $code .= <<<EOF
        if (isset(\$array['$name'])) {
            if (is_array(\$array['$name'])) {
                \$embed = \$this->get('$name');
                if (null === \$embed) {
                    \$embed = new \\{$embedded['class']}();
                    \$this->set('$name', \$embed);
                }
                \$embed->fromArray(\$array['$name']);
            } else {
                \$this->set('$name', \$array['$name']);
            }
        }

EOF;
        }
        foreach ($this->configClass['embeddeds_many'] as $name => $embedded) {
            $code .= <<<EOF
        if (isset(\$array['$name'])) {
            if (is_array(\$array['$name'])) {
                \$embed = \$this->get('$name');
                foreach (\$array['$name'] as \$a) {
                    if (is_array(\$a)) {
                        \$e = new \\{$embedded['class']}();
                        \$e->fromArray(\$a);
                    } else {
                        \$e = \$a;
                    }
                    \$embed->add(\$e);
                }
            } else {
                \$this->set('$name', \$array['$name']);
            }
        }

EOF;
        }

        $method = new Method('public', 'fromArray', '$array', $code);
        $method->setDocComment(<<<EOF
    /**
     * Import data from an array.
     *
     * @param array \$array An array.
     *
     * @return void
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * document "toArray" method
     */
    protected function processDocumentToArrayMethod()
    {
        // fields
        $fieldsCode = '';
        foreach ($this->configClass['fields'] as $name => $field) {
            $fieldsCode .= <<<EOF
         \$array['$name'] = \$this->data['fields']['$name'];

EOF;
        }

        // embeddeds
        $embeddedsCode = '';
        foreach ($this->configClass['embeddeds_one'] as $name => $embedded) {
            $embeddedsCode .= <<<EOF
            if (null === \$this->data['embeddeds']['$name']) {
                \$array['$name'] = null;
            } else {
                \$array['$name'] = \$this->data['embeddeds']['$name']->toArray();
            }

EOF;
        }
        foreach ($this->configClass['embeddeds_many'] as $name => $embedded) {
            $embeddedsCode .= <<<EOF
            if (null === \$this->data['embeddeds']['$name']) {
                \$array['$name'] = null;
            } else {
                \$array['$name'] = array();
                foreach (\$this->data['embeddeds']['$name'] as \$key => \$embed) {
                    \$array['$name'][\$key] = \$embed->toArray();
                }
            }

EOF;
        }

        $method = new Method('public', 'toArray', '$withEmbeddeds = true', <<<EOF
        \$array = array();

$fieldsCode

        if (\$withEmbeddeds) {
$embeddedsCode
        }

        return \$array;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Export the document data to an array.
     *
     * @param bool \$withEmbeddeds If export embeddeds or not.
     *
     * @return array An array with the document data.
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * Document extensions events.
     */
    protected function processDocumentExtensionsEventsMethods()
    {
        foreach (array(
            'preInsert', 'postInsert',
            'preUpdate', 'postUpdate',
            'preSave'  , 'postSave'  ,
            'preDelete', 'postDelete',
        ) as $event) {
            $code = '';
            foreach ($this->configClass['extensions_events'][$event] as $method) {
                $code .= "        \$this->$method();\n";
            }
            $this->definitions['document_base']->addMethod(new Method('public', $event.'Extensions', '', $code));
        }
    }

    /*
     * Repository "documentClass" property.
     */
    protected function processRepositoryDocumentClassProperty()
    {
        $property = new Property('protected', 'documentClass', $this->configClass['final_class']);

        $this->definitions['repository_base']->addProperty($property);
    }

    /*
     * Repository "connectionName" property.
     */
    protected function processRepositoryConnectionNameProperty()
    {
        $property = new Property('protected', 'connectionName', $this->configClass['connection']);

        $this->definitions['repository_base']->addProperty($property);
    }

    /*
     * Repository "collectionName" property.
     */
    protected function processRepositoryCollectionNameProperty()
    {
        $property = new Property('protected', 'collectionName', $this->configClass['collection']);

        $this->definitions['repository_base']->addProperty($property);
    }

    /*
     * Repository "isFile" property.
     */
    protected function processRepositoryIsFileProperty()
    {
        $property = new Property('protected', 'isFile', $this->configClass['is_file']);

        $this->definitions['repository_base']->addProperty($property);
    }

    /*
     * Repository "ensureIndexes" method.
     */
    protected function processRepositoryEnsureIndexesMethod()
    {
        $code = '';
        foreach ($this->configClass['indexes'] as $key => $index) {
            $keys    = \Mondongo\Mondator\Dumper::exportArray($index['keys'], 12);
            $options = \Mondongo\Mondator\Dumper::exportArray(array_merge(isset($index['options']) ? $index['options'] : array(), array('safe' => true)), 12);

            $code .= <<<EOF
        \$this->getCollection()->ensureIndex($keys, $options);

EOF;
        }

        $method = new Method('public', 'ensureIndexes', '', $code);
        $method->setDocComment(<<<EOF
    /**
     * Ensure indexes.
     *
     * @return void
     */
EOF
        );

        $this->definitions['repository_base']->addMethod($method);
    }

    protected function parseAndCheckAssociationClass(&$association, $name)
    {
        if (is_string($association)) {
            $association = array('class' => $association);
        }

        if (!is_array($association)) {
            throw new \RuntimeException(sprintf('The association "%s" of the class "%s" is not an array or string.', $name, $this->class));
        }
        if (!isset($association['class'])) {
            throw new \RuntimeException(sprintf('The association "%s" of the class "%s" does not have class.', $name, $this->class));
        }
        if (!is_string($association['class'])) {
            throw new \RuntimeException(sprintf('The class of the association "%s" of the class "%s" is not an string.', $name, $this->class));
        }
    }

    protected function fieldsToProcess()
    {
        $fields = $this->configClass['fields'];
        foreach ($this->configClass['references_one'] as $name => $reference) {
            $fields[$reference['field']] = array('type' => 'reference_one', 'reference' => $name);
        }
        foreach ($this->configClass['references_many'] as $name => $reference) {
            $fields[$reference['field']] = array('type' => 'reference_many', 'reference' => $name);
        }

        return $fields;
    }
}
