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
use Mondongo\Mondator\Definition\Method;
use Mondongo\Mondator\Extension;

/**
 * The Mondongo FromToArray extension.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class FromToArray extends Extension
{
    /**
     * @inheritdoc
     */
    protected function doProcess()
    {
        $this->processFromArrayMethod();
        $this->processToArrayMethod();
    }

    /*
     * "fromArray" method.
     */
    public function processFromArrayMethod()
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
     * "toArray" method
     */
    protected function processToArrayMethod()
    {
        // fields
        $fieldsCode = '';
        foreach ($this->classData['fields'] as $name => $field) {
            $fieldsCode .= <<<EOF
        if (null !== \$this->data['fields']['$name']) {
            \$array['$name'] = \$this->data['fields']['$name'];
        }

EOF;
        }

        // embeds
        $embedsCode = '';
        foreach ($this->classData['embeds'] as $name => $embed) {
            if ('one' == $embed['type']) {
                $typeCode = <<<EOF
                \$array['$name'] = \$this->data['embeds']['$name']->toArray();
EOF;
            } else {
                $typeCode = <<<EOF
                foreach (\$this->data['embeds']['$name'] as \$embed) {
                    \$array['$name'][] = \$embed->toArray();
                }
EOF;
            }

            $embedsCode .= <<<EOF
            if (null !== \$this->data['embeds']['$name']) {
$typeCode
            }

EOF;
        }

        $this->container['document_base']->addMethod(new Method('public', 'toArray', '$withEmbeds = true', <<<EOF
        \$array = array();

$fieldsCode

        if (\$withEmbeds) {
$embedsCode
        }

        return \$array;
EOF
        ));
    }
}
