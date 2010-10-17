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
 * The Mondongo PropertyOverloading extension.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class PropertyOverloading extends Extension
{
    /**
     * @inheritdoc
     */
    protected function doProcess()
    {
        $this->process__setMethod();
        $this->process__getMethod();
    }

    /*
     * "__set" method
     */
    protected function process__setMethod()
    {
        $method = new Method('public', '__set', '$name, $value', <<<EOF
        if (!isset(self::\$dataMap[\$name])) {
            throw new \InvalidArgumentException(sprintf('The name "%s" does not exists.', \$name));
        }

        \$method = 'set'.self::\$dataMap[\$name];

        \$this->\$method(\$value);
EOF
        );
        $method->setDocComment(<<<EOF
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

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * "__get" method
     */
    protected function process__getMethod()
    {
        $method = new Method('public', '__get', '$name', <<<EOF
        if (!isset(self::\$dataMap[\$name])) {
            throw new \InvalidArgumentException(sprintf('The data "%s" does not exists.', \$name));
        }

        \$method = 'get'.self::\$dataMap[\$name];

        return \$this->\$method();
EOF
        );
        $method->setDocComment(<<<EOF
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

        $this->definitions['document_base']->addMethod($method);
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
        $method->setDocComment(<<<EOF
    /**
     * Throws a \LogicException because you cannot unset data in the document.
     *
     * @throws \LogicException
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }
}
