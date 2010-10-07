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
 * The Mondongo ArrayAccess extension.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class ArrayAccess extends Extension
{
    /**
     * @inheritdoc
     */
    protected function doProcess()
    {
        $this->container['document_base']->addInterface('\ArrayAccess');

        $this->processOffsetExistsMethod();
        $this->processOffsetSetMethod();
        $this->processOffsetGetMethod();
        $this->processOffsetUnsetMethod();
    }

    /*
     * "offsetExists" method
     */
    protected function processOffsetExistsMethod()
    {
        $method = new Method('public', 'offsetExists', '$name', <<<EOF
        throw new \LogicException('You cannot check if data exists in a document.');
EOF
        );
        $method->setPHPDoc(<<<EOF
    /**
     * Throws an \LogicException because you cannot check if data exists.
     *
     * @throws \LogicException
     */
EOF
        );

        $this->container['document_base']->addMethod($method);
    }

    /*
     * "offsetSet" method
     */
    protected function processOffsetSetMethod()
    {
        $method = new Method('public', 'offsetSet', '$name, $value', <<<EOF
        if (!isset(self::\$map[\$name])) {
            throw new \InvalidArgumentException(sprintf('The name "%s" does not exists.', \$name));
        }

        \$method = 'set'.self::\$map[\$name];

        \$this->\$method(\$value);
EOF
        );
        $method->setPHPDoc(<<<EOF
    /**
     * Set data in the document.
     *
     * @param string \$name  The data name.
     * @param mixed  \$value The value.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the data name does not exists.
     */
EOF
        );

        $this->container['document_base']->addMethod($method);
    }

    /*
     * "offsetGet" method
     */
    protected function processOffsetGetMethod()
    {
        $method = new Method('public', 'offsetGet', '$name', <<<EOF
        if (!isset(self::\$map[\$name])) {
            throw new \InvalidArgumentException(sprintf('The data "%s" does not exists.', \$name));
        }

        \$method = 'get'.self::\$map[\$name];

        return \$this->\$method();
EOF
        );
        $method->setPHPDoc(<<<EOF
    /**
     * Returns data of the document.
     *
     * @param string \$name The data name.
     *
     * @return mixed Some data.
     *
     * @throws \InvalidArgumentException If the data name does not exists.
     */
EOF
        );

        $this->container['document_base']->addMethod($method);
    }

    /*
     * "offsetUnset" method
     */
    protected function processOffsetUnsetMethod()
    {
        $method = new Method('public', 'offsetUnset', '$name', <<<EOF
        throw new \LogicException('You cannot unset data in the document.');
EOF
        );
        $method->setPHPDoc(<<<EOF
    /**
     * Throws a \LogicException because you cannot unset data in the document.
     *
     * @throws \LogicException
     */
EOF
        );

        $this->container['document_base']->addMethod($method);
    }
}