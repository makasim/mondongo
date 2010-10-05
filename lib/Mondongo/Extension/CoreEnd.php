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
        $this->processDocumentRelations();

        $this->processDocumentFromArrayMethod();

        // repository
        $this->processRepositoryDocumentClassProperty();
        $this->processRepositoryConnectionNameProperty();
        $this->processRepositoryCollectionNameProperty();
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

        $this->container['document_base']->addProperty(new Property('protected', 'data', $data));
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

        $this->container['document_base']->addProperty(new Property('protected', 'fieldsModified', $this->fieldsModified));
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

        $this->container['document_base']->addMethod(new Method('public', 'setDocumentData', '$data', <<<EOF
$idCode
$fieldsCode
$embedsCode
        $resetFieldsModified
EOF
        ));
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

        $this->container['document_base']->addMethod(new Method('public', 'fieldsToMongo', '$fields', <<<EOF
$fieldsCode

        return \$fields;
EOF
        ));
    }

    /*
     * Document fields.
     */
    protected function processDocumentFields()
    {
        foreach ($this->classData['fields'] as $name => $field) {
            // set method
            $this->container['document_base']->addMethod(new Method(
                'public',
                'set'.Inflector::camelize($name),
                '$value',
                $this->getMethodCode(new \ReflectionMethod(__CLASS__, 'setField'), array('$_name_' => "'$name'"))
            ));

            // get method
            $this->container['document_base']->addMethod(new Method(
                'public',
                'get'.Inflector::camelize($name),
                '',
                "        return \$this->data['fields']['$name'];"
            ));
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
                // getter
                $getterCode = <<<EOF
        if (null === \$this->data['references']['$name']) {
            \$value = \\Mondongo\Container::getForDocumentClass('{$reference['class']}')->getRepository('{$reference['class']}')->get(\$this->$fieldGetter());
            if (!\$value) {
                throw new \RuntimeException('The reference "$name" does not exists');
            }
            \$this->data['references']['$name'] = \$value;
        }

        return \$this->data['references']['$name'];
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
            }

            $this->container['document_base']->addMethod(new Method('public', 'set'.Inflector::camelize($name), '$value', $setterCode));
            $this->container['document_base']->addMethod(new Method('public', 'get'.Inflector::camelize($name), '', $getterCode));

            // update
            if ('many' == $reference['type']) {
                $this->container['document_base']->addMethod(new Method('public', $updateMethodName, '', <<<EOF
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
EOF
            ));
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
                // getter
                $getterCode = <<<EOF
        if (null === \$this->data['embeds']['$name']) {
            \$this->data['embeds']['$name'] = new \\{$embed['class']}();
        }

        return \$this->data['embeds']['$name'];
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
                // getter
                $getterCode = <<<EOF
        if (null === \$this->data['embeds']['$name']) {
            \$this->data['embeds']['$name'] = new \\Mondongo\Group();
        }

        return \$this->data['embeds']['$name'];
EOF;
            }

            $this->container['document_base']->addMethod(new Method('public', 'set'.Inflector::camelize($name), '$value', $setterCode));
            $this->container['document_base']->addMethod(new Method('public', 'get'.Inflector::camelize($name), '', $getterCode));
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
            }

            $this->container['document_base']->addMethod(new Method('public', 'get'.Inflector::camelize($name), '', $getterCode));
        }
    }

    /*
     * Document "fromArray" method.
     */
    public function processDocumentFromArrayMethod()
    {
        $code = '';

        // fields
        foreach ($this->classData['fields'] as $name => $field) {
            $setter = 'set'.Inflector::camelize($name);
            $code .= <<<EOF
        if (isset(\$array['$name'])) {
            \$this->$setter(\$array['$name']);
        }

EOF;
        }

        // references
        foreach ($this->classData['references'] as $name => $reference) {
            $setter = 'set'.Inflector::camelize($name);

            if ('one' == $reference['type']) {
                $code .= <<<EOF
        if (isset(\$array['$name'])) {
            \$this->$setter(\$array['$name']);
        }

EOF;
            } else {
                $code .= <<<EOF
        if (isset(\$array['$name'])) {
            \$reference = \$array['$name'];
            if (is_array(\$reference)) {
                \$reference = new \Mondongo\Group(\$reference);
            }
            \$this->$setter(\$reference);
        }

EOF;
            }
        }

        // embeds
        foreach ($this->classData['embeds'] as $name => $embed) {
            $setter = 'set'.Inflector::camelize($name);
            $getter = 'get'.Inflector::camelize($name);

            if ('one' == $embed['type']) {
                $typeCode = <<<EOF
                \$embed->fromArray(\$array['$name']);
EOF;
            } else {
                $typeCode = <<<EOF
                foreach (\$array['$name'] as \$a) {
                    if (is_array(\$a)) {
                        \$e = new \\{$embed['class']}();
                        \$e->fromArray(\$a);
                    } else {
                        \$e = \$a;
                    }
                    \$embed->add(\$e);
                }
EOF;
            }

            $code .= <<<EOF
        if (isset(\$array['$name'])) {
            if (is_array(\$array['$name'])) {
                \$embed = \$this->$getter();
$typeCode
            } else {
                \$this->$setter(\$array['$name']);
            }
        }

EOF;
        }

        $this->container['document_base']->addMethod(new Method('public', 'fromArray', '$array', $code));
    }

    /*
     * Repository "documentClass" property.
     */
    protected function processRepositoryDocumentClassProperty()
    {
        $this->container['repository_base']->addProperty(new Property('protected', 'documentClass', $this->container['document']->getFullClass()));
    }

    /*
     * Repository "connectionName" property.
     */
    protected function processRepositoryConnectionNameProperty()
    {
        $this->container['repository_base']->addProperty(new Property('protected', 'connectionName', $this->classData['connection']));
    }

    /*
     * Repository "collectionName" property.
     */
    protected function processRepositoryCollectionNameProperty()
    {
        $this->container['repository_base']->addProperty(new Property('protected', 'collectionName', $this->classData['collection']));
    }
}
