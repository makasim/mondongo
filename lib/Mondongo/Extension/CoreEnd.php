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

use Mondongo\Inflector;
use Mondongo\Mondator\Definition\Container;
use Mondongo\Mondator\Definition\Definition;
use Mondongo\Mondator\Definition\Method;
use Mondongo\Mondator\Definition\Property;
use Mondongo\Mondator\Extension;
use Mondongo\Type\Container as TypeContainer;

/**
 * The Mondongo CoreEnd extension.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class CoreEnd extends Extension
{
    protected $fieldsModified;

    /**
     * @inheritdoc
     */
    protected function doProcess()
    {
        $this->processParseFields();

        // document
        $this->processDocumentDataProperty();
        $this->processDocumentFieldsModifiedsProperty();

        $this->processDocumentMapProperty();
        $this->processDocumentGetMapMethod();

        $this->processDocumentSetDocumentDataMethod();
        $this->processDocumentFieldsToMongoMethod();
        $this->processDocumentFields();
        $this->processDocumentReferences();
        $this->processDocumentEmbeds();
        if (!$this->classData['embed']) {
            $this->processDocumentRelations();
        }

        $this->processDocumentExtensionsEventsMethods();

        // repository
        if (!$this->classData['embed']) {
            $this->processRepositoryDocumentClassProperty();
            $this->processRepositoryConnectionNameProperty();
            $this->processRepositoryCollectionNameProperty();
            $this->processRepositoryEnsureIndexesMethod();
        }
    }

    /*
     * Parse Fields.
     */
    protected function processParseFields()
    {
        foreach ($this->classData['fields'] as &$field) {
            if (is_string($field)) {
                $field = array('type' => $field);
            }
        }
    }

    /*
     * Document "data" property.
     */
    protected function processDocumentDataProperty()
    {
        $data = array();

        // fields
        foreach ($this->classData['fields'] as $name => $field) {
            $data['fields'][$name] = isset($field['default']) ? $field['default'] : null;
        }

        // references
        foreach ($this->classData['references'] as $name => $reference) {
            $data['references'][$name] = null;
        }

        // embeds
        foreach ($this->classData['embeds'] as $name => $embed) {
            $data['embeds'][$name] = null;
        }

        // relations
        foreach ($this->classData['relations'] as $name => $relation) {
            $data['relations'][$name] = null;
        }

        $property = new Property('protected', 'data', $data);

        $this->container['document_base']->addProperty($property);
    }

    /*
     * Document "fieldsModified" property.
     */
    protected function processDocumentFieldsModifiedsProperty()
    {
        $this->fieldsModified = array();
        foreach ($this->classData['fields'] as $name => $field) {
            if (isset($field['default'])) {
                $this->fieldsModified[$name] = null;
            }
        }

        $property = new Property('protected', 'fieldsModified', $this->fieldsModified);

        $this->container['document_base']->addProperty($property);
    }

    /*
     * Document "map" property.
     */
    protected function processDocumentMapProperty()
    {
        $map = array();

        // fields
        foreach ($this->classData['fields'] as $name => $field) {
            $map[$name] = Inflector::camelize($name);
        }

        // references
        foreach ($this->classData['references'] as $name => $reference) {
            $map[$name] = Inflector::camelize($name);
        }

        // embeds
        foreach ($this->classData['embeds'] as $name => $embed) {
            $map[$name] = Inflector::camelize($name);
        }

        // relations
        foreach ($this->classData['relations'] as $name => $relation) {
            $map[$name] = Inflector::camelize($name);
        }

        $property = new Property('protected', 'map', $map);
        $property->setIsStatic(true);

        $this->container['document_base']->addProperty($property);
    }

    /*
     * Document "getMap" method.
     */
    public function processDocumentGetMapMethod()
    {
        $method = new Method('public', 'getMap', '', <<<EOF
        return self::\$map;
EOF
        );
        $method->setIsStatic(true);
        $method->setDocComment(<<<EOF
    /**
     * Returns the fields map.
     *
     * @return array The fields map.
     */
EOF
        );

        $this->container['document_base']->addMethod($method);
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
        if ($this->classData['embed']) {
            $idCode = '';
        }

        // fields
        $fieldsCode = '';
        foreach ($this->classData['fields'] as $name => $field) {
            $typeCode = strtr(TypeContainer::getType($field['type'])->toPHPInString(), array(
                '%from%' => "\$data['$name']",
                '%to%'   => "\$this->data['fields']['$name']",
            ));

            $fieldsCode .= <<<EOF
        if (isset(\$data['$name'])) {
            $typeCode
        }

EOF;
        }

        // embeds
        $embedsCode = '';
        foreach ($this->classData['embeds'] as $name => $embed) {
            $embedSetter = 'set'.Inflector::camelize($name);
            // one
            if ('one' == $embed['type']) {
                $embedsCode .= <<<EOF
        if (isset(\$data['$name'])) {
            \$embed = new \\{$embed['class']}();
            \$embed->setDocumentData(\$data['$name']);
            \$this->$embedSetter(\$embed);
        }

EOF;
            // many
            } elseif ('many' == $embed['type']) {
                $embedsCode .= <<<EOF
        if (isset(\$data['$name'])) {
            \$elements = array();
            foreach (\$data['$name'] as \$datum) {
                \$elements[] = \$element = new \\{$embed['class']}();
                \$element->setDocumentData(\$datum);
            }
            \$group = new \Mondongo\Group(\$elements);
            \$group->saveOriginalElements();
            \$this->$embedSetter(\$group);
        }

EOF;
            }
        }

        $resetFieldsModified = $this->fieldsModified ? "\$this->fieldsModified = array();" : '';

        $method = new Method('public', 'setDocumentData', '$data', <<<EOF
$idCode
$fieldsCode
$embedsCode
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

        $this->container['document_base']->addMethod($method);
    }

    /*
     * Document "fieldsToMongo" method.
     */
    public function processDocumentFieldsToMongoMethod()
    {
        $fieldsCode = '';
        foreach ($this->classData['fields'] as $name => $field) {
            $typeCode = strtr(TypeContainer::getType($field['type'])->toMongoInString(), array(
                '%from%' => "\$fields['$name']",
                '%to%'   => "\$fields['$name']",
            ));

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

        $this->container['document_base']->addMethod($method);
    }

    /*
     * Document fields.
     */
    protected function processDocumentFields()
    {
        foreach ($this->classData['fields'] as $name => $field) {
            // set method
            $method = new Method(
                'public',
                'set'.Inflector::camelize($name),
                '$value',
                $this->getMethodCode(new \ReflectionMethod(__CLASS__, 'setField'), array('$_name_' => "'$name'"))
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

            $this->container['document_base']->addMethod($method);

            // get method
            $method = new Method(
                'public',
                'get'.Inflector::camelize($name),
                '',
                "        return \$this->data['fields']['$name'];"
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the "$name" field.
     *
     * @return mixed The $name field.
     */
EOF
            );

            $this->container['document_base']->addMethod($method);
        }
    }

    private function setField($value)
    {
        if (!array_key_exists($_name_, $this->fieldsModified)) {
            $this->fieldsModified[$_name_] = $this->data['fields'][$_name_];
        } elseif ($value === $this->fieldsModified[$_name_]) {
            unset($this->fieldsModified[$_name_]);
        }

        $this->data['fields'][$_name_] = $value;
    }

    /*
     * Document references.
     */
    protected function processDocumentReferences()
    {
        foreach ($this->classData['references'] as $name => $reference) {
            $fieldSetter = 'set'.Inflector::camelize($reference['field']);
            $fieldGetter = 'get'.Inflector::camelize($reference['field']);

            $updateMethodName = 'update'.Inflector::camelize($name);

            /*
             * One
             */
            if ('one' == $reference['type']) {
                // setter
                $setterCode = <<<EOF
        if (!\$value instanceof \\{$reference['class']}) {
            throw new \InvalidArgumentException('The reference "$name" is not an instance of "{$reference['class']}".');
        }
        if (\$value->isNew()) {
            throw new \InvalidArgumentException('The reference "$name" is new.');
        }

        \$this->{$fieldSetter}(\$value->getId());
        \$this->data['references']['$name'] = \$value;
EOF;
                $setterDocComment = <<<EOF
    /**
     * Set the "$name" reference.
     *
     * @param {$reference['class']} \$value The value.
     *
     * @return void
     */
EOF;
                // getter
                $getterCode = <<<EOF
        if (null === \$this->data['references']['$name']) {
            \$value = \\Mondongo\Container::getForDocumentClass('{$reference['class']}')
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
            /*
             * Many
             */
            } else {
                // setter
                $setterCode = <<<EOF
        if (!\$value instanceof \Mondongo\Group) {
            throw new \InvalidArgumentException('The reference "$name" is not an instance of Mondongo\Group.');
        }
        \$value->setChangeCallback(array(\$this, '$updateMethodName'));

        \$ids = array();
        foreach (\$value as \$document) {
            if (!\$document instanceof \\{$reference['class']}) {
                throw new \InvalidArgumentException('Some document in the reference "$name" is not an instance of "{$reference['class']}".');
            }
            if (\$document->isNew()) {
                throw new \InvalidArgumentException('Some document in the reference "$name" is new.');
            }
            \$ids[] = \$document->getId();
        }

        \$this->{$fieldSetter}(\$ids);
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
                // getter
                $getterCode = <<<EOF
        if (null === \$this->data['references']['$name']) {
            \$ids   = \$this->$fieldGetter();
            \$value = \\Mondongo\Container::getForDocumentClass('{$reference['class']}')->getRepository('{$reference['class']}')->find(array(
                'query' => array('_id' => array('\$in' => \$ids)),
            ));
            if (!\$value || count(\$value) != count(\$ids)) {
                throw new \RuntimeException('The reference "$name" does not exists');
            }

            \$group = new \Mondongo\Group(\$value);
            \$group->setChangeCallback(array(\$this, '$updateMethodName'));

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
            }

            // setter
            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', $setterCode);
            $method->setDocComment($setterDocComment);
            $this->container['document_base']->addMethod($method);

            // getter
            $method = new Method('public', 'get'.Inflector::camelize($name), '', $getterCode);
            $method->setDocComment($setterDocComment);
            $this->container['document_base']->addMethod($method);

            // update
            if ('many' == $reference['type']) {
                $updateCode = <<<EOF
        if (null !== \$this->data['references']['$name']) {
            \$ids = array();
            foreach (\$this->data['references']['$name'] as \$document) {
                if (!\$document instanceof \\{$reference['class']}) {
                    throw new \RuntimeException('Some document of the "$name" reference is not an instance of "{$reference['class']}".');
                }
                if (\$document->isNew()) {
                    throw new \RuntimeException('Some document of the "$name" reference is new.');
                }
                \$ids[] = \$document->getId();
            }

            if (\$ids !== \$this->$fieldGetter()) {
                \$this->$fieldSetter(\$ids);
            }
        }
EOF;
                $updateDocComment = <<<EOF
    /**
     * Update the "$name" reference.
     *
     * @return void
     */
EOF;

                $method = new Method('public', $updateMethodName, '', $updateCode);
                $method->setDocComment($updateDocComment);
                $this->container['document_base']->addMethod($method);
            }
        }
    }

    /*
     * Document embeds.
     */
    protected function processDocumentEmbeds()
    {
        foreach ($this->classData['embeds'] as $name => $embed) {
            /*
             * one
             */
            if ('one' == $embed['type']) {
                // setter
                $setterCode = <<<EOF
        if (!\$value instanceof \\{$embed['class']}) {
            throw new \InvalidArgumentException('The embed "$name" is not an instance of "{$embed['class']}".');
        }

        \$this->data['embeds']['$name'] = \$value;
EOF;
                $setterDocComment = <<<EOF
    /**
     * Set the "$name" embed.
     *
     * @param {$embed['class']} \$value The embed.
     *
     * @return void
     */
EOF;
                // getter
                $getterCode = <<<EOF
        if (null === \$this->data['embeds']['$name']) {
            \$this->data['embeds']['$name'] = new \\{$embed['class']}();
        }

        return \$this->data['embeds']['$name'];
EOF;
                $getterDocComment = <<<EOF
    /**
     * Returns the "$name" embed..
     *
     * @return {$embed['class']} The "$name" embed.
     */
EOF;
            /*
             * many
             */
            } else {
                // setter
                $setterCode = <<<EOF
        if (!\$value instanceof \Mondongo\Group) {
            throw new \InvalidArgumentException('The embed "$name" is not an instance of "Mondongo\Group".');
        }

        \$this->data['embeds']['$name'] = \$value;
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
                // getter
                $getterCode = <<<EOF
        if (null === \$this->data['embeds']['$name']) {
            \$this->data['embeds']['$name'] = new \\Mondongo\Group();
        }

        return \$this->data['embeds']['$name'];
EOF;
                $getterDocComment = <<<EOF
    /**
     * Returns the "$name" embed.
     *
     * @return Mondongo\Group The "$name" embed.
     */
EOF;
            }

            // setter
            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', $setterCode);
            $method->setDocComment($setterDocComment);
            $this->container['document_base']->addMethod($method);

            // getter
            $method = new Method('public', 'get'.Inflector::camelize($name), '', $getterCode);
            $method->setDocComment($getterDocComment);
            $this->container['document_base']->addMethod($method);
        }
    }

    /*
     * Document relations.
     */
    protected function processDocumentRelations()
    {
        foreach ($this->classData['relations'] as $name => $relation) {
            /*
             * one
             */
            if ('one' == $relation['type']) {
                $getterCode = <<<EOF
        if (null === \$this->data['relations']['$name']) {
            \$this->data['relations']['$name'] = \Mondongo\Container::getForDocumentClass('{$relation['class']}')->getRepository('{$relation['class']}')->find(array(
                'query' => array('{$relation['field']}' => \$this->getId()),
                'one'   => true,
            ));
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
            /*
             * many
             */
            } else {
                $getterCode = <<<EOF
        if (null === \$this->data['relations']['$name']) {
            \$this->data['relations']['$name'] = \Mondongo\Container::getForDocumentClass('{$relation['class']}')->getRepository('{$relation['class']}')->find(array(
                'query' => array('{$relation['field']}' => \$this->getId()),
            ));
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
            }

            $method = new Method('public', 'get'.Inflector::camelize($name), '', $getterCode);
            $method->setDocComment($getterDocComment);
            $this->container['document_base']->addMethod($method);
        }
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
            foreach ($this->classData['extensions_events'][$event] as $method) {
                $code .= "        \$this->$method();\n";
            }
            $this->container['document_base']->addMethod(new Method('public', $event.'Extensions', '', $code));
        }
    }

    /*
     * Repository "documentClass" property.
     */
    protected function processRepositoryDocumentClassProperty()
    {
        $property = new Property('protected', 'documentClass', $this->container['document']->getFullClass());

        $this->container['repository_base']->addProperty($property);
    }

    /*
     * Repository "connectionName" property.
     */
    protected function processRepositoryConnectionNameProperty()
    {
        $property = new Property('protected', 'connectionName', $this->classData['connection']);

        $this->container['repository_base']->addProperty($property);
    }

    /*
     * Repository "collectionName" property.
     */
    protected function processRepositoryCollectionNameProperty()
    {
        $property = new Property('protected', 'collectionName', $this->classData['collection']);

        $this->container['repository_base']->addProperty($property);
    }

    /*
     * Repository "ensureIndexes" method.
     */
    protected function processRepositoryEnsureIndexesMethod()
    {
        $code = '';
        foreach ($this->classData['indexes'] as $key => $index) {
            $keys    = var_export($index['keys'], true);
            $options = var_export(array_merge(isset($index['options']) ? $index['options'] : array(), array('safe' => true)), true);

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

        $this->container['repository_base']->addMethod($method);
    }
}
