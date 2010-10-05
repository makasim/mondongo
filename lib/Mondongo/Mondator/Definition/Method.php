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

namespace Mondongo\Mondator\Definition;

/**
 * Represents a method of a class.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Method
{
    protected $visibility;

    protected $name;

    protected $arguments;

    protected $code;

    protected $isStatic;

    protected $isAbstract;

    /**
     * Constructor.
     *
     * @param string $visibility The visibility.
     * @param string $name       The name.
     * @param string $arguments  The arguments (as string).
     * @param string $code       The code.
     * @param bool   $isStatic   If the method is static (optional, false by default).
     * @param bool   $isAbstract If the method is abstract (optional, false by default).
     *
     * @return void
     */
    public function __construct($visibility, $name, $arguments, $code, $isStatic = false, $isAbstract = false)
    {
        $this->setVisibility($visibility);
        $this->setName($name);
        $this->setArguments($arguments);
        $this->setCode($code);
        $this->setIsStatic($isStatic);
        $this->setIsAbstract($isAbstract);
    }

    /**
     * Set the visibility.
     *
     * @param string $visibility The visibility.
     *
     * @return void
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }

    /**
     * Returns the visibility.
     *
     * @return string The visibility.
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set the name.
     *
     * @param string $name The name.
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the name.
     *
     * @return string The name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the arguments.
     *
     * Example: "$argument1, &$argument2"
     *
     * @param string $arguments The arguments (as string).
     *
     * @return void
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Returns the arguments.
     *
     * @return void
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Set the code.
     *
     * @param string $code.
     *
     * @return void
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Returns the code.
     *
     * @return string The code.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set if the method is static.
     *
     * @param bool $isStatic If the method is static.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the $isStatic is not a boolean.
     */
    public function setIsStatic($isStatic)
    {
        if (!is_bool($isStatic)) {
            throw new \InvalidArgumentException('The $isStatic is not a boolean.');
        }

        $this->isStatic = $isStatic;
    }

    /**
     * Return if the method is static.
     *
     * @return bool Returns if the method is static.
     */
    public function getIsStatic()
    {
        return $this->isStatic;
    }

    /**
     * Set if the method is abstract.
     *
     * @param bool $isAbstract If the method is abstract.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the $isAbstract is not a boolean.
     */
    public function setIsAbstract($isAbstract)
    {
        if (!is_bool($isAbstract)) {
            throw new \InvalidArgumentException('The $isAbstract is not a boolean.');
        }

        $this->isAbstract = $isAbstract;
    }

    /**
     * Return if the method is abstract.
     *
     * @return bool Returns if the method is abstract.
     */
    public function getIsAbstract()
    {
        return $this->isAbstract;
    }
}