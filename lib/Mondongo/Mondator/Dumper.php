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

namespace Mondongo\Mondator;

use Mondongo\Mondator\Definition\Definition;

/**
 * The Mondator Dumper.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Dumper
{
    protected $definition;

    /**
     * Constructor.
     *
     * @param Mondongo\Mondator\Definition\Definition $definition The definition.
     *
     * @return void
     */
    public function __construct(Definition $definition)
    {
        $this->setDefinition($definition);
    }

    /**
     * Set the definition.
     *
     * @param Mondongo\Mondator\Definition\Definition $definition The definition.
     *
     * @return void
     */
    public function setDefinition(Definition $definition)
    {
        $this->definition = $definition;
    }

    /**
     * Returns the definition
     *
     * @return Mondongo\Mondator\Definition\Definition The definition.
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Dump the definition.
     *
     * @return string The PHP code of the definition.
     */
    public function dump()
    {
        return
            $this->startFile().
            $this->addNamespace().
            $this->startClass().
            $this->addProperties().
            $this->addMethods().
            $this->endClass()
        ;
    }

    protected function startFile()
    {
        return <<<EOF
<?php
EOF;
    }

    protected function addNamespace()
    {
        if (!$namespace = $this->definition->getNamespace()) {
            return '';
        }

        return <<<EOF


namespace $namespace;
EOF;
    }

    protected function startClass()
    {
        $declaration = '';

        // PHPDoc
        $PHPDoc = $this->definition->getPHPDoc();

        // abstract
        $declaration .= $this->definition->getIsAbstract() ? 'abstract ' : '';

        // class
        $declaration .= 'class '.$this->definition->getClassName();

        // parent class
        if ($parentClass = $this->definition->getParentClass()) {
            $declaration .= ' extends '.$parentClass;
        }

        // interfaces
        if ($interfaces = $this->definition->getInterfaces()) {
            $declaration .= ' implements'.implode(', ', $interfaces);
        }

        return <<<EOF


$PHPDoc
$declaration
{
EOF;
    }

    protected function addProperties()
    {
        $code = '';

        foreach ($this->definition->getProperties() as $property) {
            $PHPDoc   = $property->getPHPDoc();
            $isStatic = $property->getIsStatic() ? 'static ' : '';
            $value    = var_export($property->getValue(), true);

            $code .= <<<EOF


$PHPDoc
    $isStatic{$property->getVisibility()} \${$property->getName()} = $value;
EOF;
        }

        return $code;
    }

    protected function addMethods()
    {
        $code = '';

        foreach ($this->definition->getMethods() as $method) {
            $PHPDoc   = $method->getPHPDoc();
            $isStatic = $method->getIsStatic() ? 'static ' : '';

            if ($method->getIsAbstract()) {
                $code .= <<<EOF


$PHPDoc
    abstract $isStatic{$method->getVisibility()} function {$method->getName()}({$method->getArguments()});
EOF;
            } else {
                $code .= <<<EOF


$PHPDoc
    $isStatic{$method->getVisibility()} function {$method->getName()}({$method->getArguments()})
    {
{$method->getCode()}
    }
EOF;
        }
            }

        return $code;
    }

    protected function endClass()
    {
        return <<<EOF

}
EOF;
    }
}
