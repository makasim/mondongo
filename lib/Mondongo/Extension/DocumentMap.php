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

use Mondongo\Mondator\Definition\Definition;
use Mondongo\Mondator\Definition\Method;
use Mondongo\Mondator\Definition\Property;
use Mondongo\Mondator\Extension;
use Mondongo\Mondator\Output\Output;

/**
 * Add the document map to documents.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class DocumentMap extends Extension
{
    /**
     * @inheritdoc
     */
    protected function doProcess()
    {
        $this->processInitDefinitionAndOutput();

        $this->processDocumentMapMapProperty();
        $this->processDocumentMapGetMapMethod();
    }

    /*
     * Init definition and output.
     */
    protected function processInitDefinitionAndOutput()
    {
        // definition
        if ($namespace = $this->definitions['document_base']->getNamespace()) {
            $className = $this->className;
            $namespace = substr($namespace, -4).'Map';
        } else {
            $className = $this->className.'Map';
        }

        $this->definitions['document_map'] = $definition = new Definition($className);
        $definition->setNamespace($namespace);
        $definition->setDocComment(<<<EOF
/**
 * Map of the {$this->className} document.
 */
EOF
        );

        // output
        $this->outputs['document_map'] = new Output($this->outputs['document']->getDir().'/Map', true);
    }

    /*
     * DocumentMap "map" property.
     */
    protected function processDocumentMapMapProperty()
    {
        $map = array();

        // fields
        $map['fields'] = $this->configClass['fields'];

        // references
        $map['references'] = $this->configClass['references'];

        // embeddeds
        $map['embeddeds'] = $this->configClass['embeddeds'];

        // relations
        if (!$this->configClass['is_embedded']) {
            $map['relations'] = $this->configClass['relations'];
        }

        $property = new Property('protected', 'map', $map);
        $property->setIsStatic(true);

        $this->definitions['document_map']->addProperty($property);
    }

    /*
     * DocumentMap "getMap" method.
     */
    protected function processDocumentMapGetMapMethod()
    {
        $method = new Method('public', 'getMap', '', <<<EOF
        return self::\$map;
EOF
        );
        $method->setIsStatic(true);
        $method->setDocComment(<<<EOF
    /**
     * Returns the map.
     *
     * @return array The data map.
     */
EOF
        );

        $this->definitions['document_map']->addMethod($method);
    }
}
